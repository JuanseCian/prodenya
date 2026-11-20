use Illuminate\Support\Facades\Route;

test('the legacy dashboard route is no longer registered', function () {
    expect(Route::has('dashboard'))->toBeFalse();
});
