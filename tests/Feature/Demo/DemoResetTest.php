<?php

use App\Services\Demo\DemoReset;
use App\Support\DemoState;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->dir = storage_path('framework/testing/demo-'.uniqid());
    File::ensureDirectoryExists($this->dir);
    config([
        'demo.snapshot_path' => $this->dir.'/snapshot.sqlite',
        'demo.state_path' => $this->dir.'/state.json',
    ]);
});

afterEach(function () {
    File::deleteDirectory($this->dir);
});

/** A tiny SQLite file with one sale, standing in for a real snapshot. */
function tinyDatabase(string $path, string $saleDate): void
{
    @unlink($path);
    $pdo = new PDO('sqlite:'.$path);
    $pdo->exec('CREATE TABLE sales (id INTEGER PRIMARY KEY, date TEXT, refunded_at TEXT, created_at TEXT, updated_at TEXT, note TEXT)');
    $pdo->exec("INSERT INTO sales (date, created_at, updated_at, note) VALUES ('{$saleDate}', '{$saleDate}', '{$saleDate}', 'clean')");
}

function rows(string $path, string $sql): array
{
    return (new PDO('sqlite:'.$path))->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

test('restore puts the snapshot back over a changed database', function () {
    $snapshot = config('demo.snapshot_path');
    $target = $this->dir.'/live.sqlite';
    tinyDatabase($snapshot, '2026-10-27 10:00:00');
    copy($snapshot, $target);
    (new PDO('sqlite:'.$target))->exec("INSERT INTO sales (date, note) VALUES ('2026-10-27 11:00:00', 'visitor sale')");

    expect(app(DemoReset::class)->restore($target))->toBeTrue()
        ->and(rows($target, 'select note from sales'))->toBe([['note' => 'clean']])
        ->and(DemoState::lastResetAt())->not->toBeNull()
        ->and(file_exists($target.'.restoring'))->toBeFalse();
});

test('restore moves history forward so the latest sale was 15 minutes ago', function () {
    $this->travelTo(now()->setDate(2026, 10, 27)->setTime(15, 0, 0));
    $snapshot = config('demo.snapshot_path');
    tinyDatabase($snapshot, '2026-10-24 10:00:00');
    $target = $this->dir.'/live.sqlite';
    touch($target);

    app(DemoReset::class)->restore($target);

    expect(rows($target, 'select date, created_at, refunded_at from sales'))
        ->toBe([['date' => '2026-10-27 14:45:00', 'created_at' => '2026-10-27 14:45:00', 'refunded_at' => null]]);
});

test('restore never pushes history into the future', function () {
    $this->travelTo(now()->setDate(2026, 10, 27)->setTime(15, 0, 0));
    $snapshot = config('demo.snapshot_path');
    tinyDatabase($snapshot, '2026-10-27 14:55:00'); // already newer than 15 minutes ago
    $target = $this->dir.'/live.sqlite';
    touch($target);

    app(DemoReset::class)->restore($target);

    expect(rows($target, 'select date from sales'))->toBe([['date' => '2026-10-27 14:55:00']]);
});

test('restore does nothing without a snapshot or a SQLite file database', function () {
    $reset = app(DemoReset::class);

    expect($reset->restore($this->dir.'/live.sqlite'))->toBeFalse();

    tinyDatabase(config('demo.snapshot_path'), '2026-10-27 10:00:00');
    expect($reset->liveDatabasePath())->toBeNull() // tests run on :memory:
        ->and($reset->restore())->toBeFalse();
});

test('the next reset is one interval after the last one', function () {
    $this->travelTo(now()->setDate(2026, 10, 27)->setTime(15, 10));
    DemoState::markReset();

    expect(DemoState::nextResetAt()->format('H:i'))->toBe('16:10')
        ->and(DemoState::isDue())->toBeFalse();

    $this->travel(61)->minutes();
    expect(DemoState::isDue())->toBeTrue();
});

test('demo:snapshot builds a real seeded snapshot', function () {
    $this->artisan('demo:snapshot')->assertSuccessful();

    $snapshot = config('demo.snapshot_path');
    expect(file_exists($snapshot))->toBeTrue()
        ->and(file_exists($snapshot.'.json'))->toBeTrue()
        ->and((int) rows($snapshot, 'select count(*) c from users')[0]['c'])->toBe(7)
        ->and((int) rows($snapshot, 'select count(*) c from sales')[0]['c'])->toBeGreaterThan(100);
})->group('slow');

test('the Reset data button resets once, then is limited for 5 minutes', function () {
    $mock = Mockery::mock(DemoReset::class);
    $mock->shouldReceive('restore')->once()->andReturnTrue();
    $this->app->instance(DemoReset::class, $mock);

    $branch = makeBranch();
    $manager = userWithRole('manager', ['phone' => config('demo.users.manager.phone')]);
    $this->actingAs($manager)->withSession(branchSession($branch) + ['demo' => ['role' => 'manager']]);

    $this->post('/demo/reset')->assertRedirect(route('dashboard'))->assertSessionHas('success');
    $this->post('/demo/reset')->assertSessionHas('error');
});

test('the banner shows the Reset data button', function () {
    $branch = makeBranch();
    $manager = userWithRole('manager');

    $this->actingAs($manager)->withSession(branchSession($branch) + ['demo' => ['role' => 'manager']])
        ->get('/dashboard')->assertSee('Reset data');
});

test('with auto reset on, the first request after the interval resets the data', function () {
    $mock = Mockery::mock(DemoReset::class);
    $mock->shouldReceive('snapshotExists')->andReturnTrue();
    $mock->shouldReceive('restore')->once()->andReturnTrue();
    $this->app->instance(DemoReset::class, $mock);

    config(['demo.auto_reset' => true]);
    DemoState::markReset(now()->subHours(2));

    $this->get('/');
});

test('with auto reset off (the default), requests never reset the data', function () {
    $mock = Mockery::mock(DemoReset::class);
    $mock->shouldReceive('restore')->never();
    $mock->shouldReceive('snapshotExists')->andReturnTrue();
    $this->app->instance(DemoReset::class, $mock);

    DemoState::markReset(now()->subHours(2));

    expect(config('demo.auto_reset'))->toBeFalse();
    $this->get('/');
});

test('messages from demo actions are shown on app pages', function () {
    $branch = makeBranch();
    $manager = userWithRole('manager');
    $session = branchSession($branch) + ['demo' => ['role' => 'manager']];

    $this->actingAs($manager)->withSession($session + ['success' => 'Demo data reset to the starting sample data.'])
        ->get('/dashboard')->assertSee('Demo data reset to the starting sample data.');
    $this->actingAs($manager)->withSession($session + ['error' => 'The demo was reset recently. Try again in 5 min.'])
        ->get('/dashboard')->assertSee('The demo was reset recently');
});
