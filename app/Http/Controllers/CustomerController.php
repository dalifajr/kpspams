<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Area;
use App\Models\ChangeLog;
use App\Models\Customer;
use App\Models\Golongan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $this->authorizeAccess($user);

        $filters = [
            'area' => $request->query('area', 'all'),
            'name' => $request->query('q', ''),
        ];

        $customersQuery = Customer::query()
            ->with(['area', 'golongan'])
            ->forUser($user)
            ->orderBy('name');

        if ($filters['area'] !== 'all') {
            $customersQuery->where('area_id', (int) $filters['area']);
        }

        if ($filters['name'] !== '') {
            $customersQuery->where(function ($query) use ($filters) {
                $query->where('name', 'like', '%' . $filters['name'] . '%')
                    ->orWhere('customer_code', 'like', '%' . $filters['name'] . '%')
                    ->orWhere('address_short', 'like', '%' . $filters['name'] . '%');
            });
        }

        $customers = $customersQuery->get();
        $areas = Area::orderBy('name')->get();

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
            'filters' => $filters,
            'areas' => $areas,
            'stats' => $this->buildStats($customers, $areas, $user),
        ]);
    }

    public function create(Request $request): Response
    {
        $user = $request->user();
        $this->authorizeAccess($user);

        return Inertia::render('Customers/Create', [
            'areas' => $this->availableAreas($user),
            'golongans' => Golongan::orderBy('name')->get(),
            'defaultCode' => $this->generateCustomerCode(),
        ]);
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $golongan = Golongan::findOrFail($request->integer('golongan_id'));

        $customer = Customer::create([
            'customer_code' => $request->string('customer_code')->trim()->upper()->toString(),
            'name' => Str::title($request->string('name')->trim()->toString()),
            'address_short' => $request->string('address_short')->trim()->toString(),
            'phone_number' => $request->filled('phone_number') ? $request->string('phone_number')->trim()->toString() : null,
            'area_id' => $request->integer('area_id'),
            'golongan_id' => $golongan->id,
            'family_members' => $request->integer('family_members'),
            'meter_reading' => $request->has('meter_reading') ? (float) $request->input('meter_reading') : null,
        ]);

        ChangeLog::record($request->user(), 'customer.create', 'Menambahkan pelanggan baru.', [
            'subject_type' => Customer::class,
            'subject_id' => $customer->id,
            'after' => $customer->only(['id', 'customer_code', 'name', 'area_id', 'golongan_id']),
        ]);

        $this->updateAreaCounters($customer->area);

        return redirect()->route('menu.customers.show', $customer)->with('status', 'Data pelanggan berhasil ditambahkan.');
    }

    public function show(Request $request, Customer $customer): Response
    {
        $user = $request->user();
        $this->authorizeAccess($user, $customer);

        $customer->load('area', 'users', 'golongan');

        return Inertia::render('Customers/Show', [
            'customer' => $customer,
            'areas' => $this->availableAreas($user),
            'golongans' => Golongan::orderBy('name')->get(),
        ]);
    }

    public function edit(Request $request, Customer $customer): Response
    {
        $user = $request->user();
        $this->authorizeAccess($user, $customer);

        return Inertia::render('Customers/Edit', [
            'customer' => $customer,
            'areas' => $this->availableAreas($user),
            'golongans' => Golongan::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $oldArea = $customer->area;
        $golongan = Golongan::findOrFail($request->integer('golongan_id'));

        $before = $customer->only(['customer_code', 'name', 'address_short', 'phone_number', 'area_id', 'golongan_id', 'family_members', 'meter_reading']);

        $customer->update([
            'customer_code' => $request->string('customer_code')->trim()->upper()->toString(),
            'name' => Str::title($request->string('name')->trim()->toString()),
            'address_short' => $request->string('address_short')->trim()->toString(),
            'phone_number' => $request->filled('phone_number') ? $request->string('phone_number')->trim()->toString() : null,
            'area_id' => $request->integer('area_id'),
            'golongan_id' => $golongan->id,
            'family_members' => $request->integer('family_members'),
            'meter_reading' => $request->has('meter_reading') ? (float) $request->input('meter_reading') : null,
        ]);

        ChangeLog::record($request->user(), 'customer.update', 'Memperbarui data pelanggan.', [
            'subject_type' => Customer::class,
            'subject_id' => $customer->id,
            'before' => $before,
            'after' => $customer->only(['customer_code', 'name', 'address_short', 'phone_number', 'area_id', 'golongan_id', 'family_members', 'meter_reading']),
            'undo' => [
                'type' => 'update',
                'model' => Customer::class,
                'id' => $customer->id,
                'data' => $before,
            ],
        ]);

        if ($oldArea->id !== $customer->area_id) {
            $this->updateAreaCounters($oldArea);
        }

        $this->updateAreaCounters($customer->area);

        return redirect()->route('menu.customers.show', $customer)->with('status', 'Data pelanggan diperbarui.');
    }

    public function destroy(Request $request, Customer $customer): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeAccess($user, $customer);

        $area = $customer->area;
        $before = $customer->only(['id', 'customer_code', 'name']);
        $customer->delete();
        $this->updateAreaCounters($area);

        ChangeLog::record($request->user(), 'customer.delete', 'Menghapus pelanggan.', [
            'subject_type' => Customer::class,
            'subject_id' => $customer->id,
            'before' => $before,
        ]);

        return redirect()->route('menu.customers.index')->with('status', 'Data pelanggan dihapus.');
    }

    public function createAccount(Request $request, Customer $customer): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeAccess($user, $customer);

        if ($customer->users()->where('role', User::ROLE_USER)->exists()) {
            return back()->withErrors([
                'account' => 'Pelanggan sudah memiliki akun aktif.',
            ]);
        }

        $validated = $request->validate([
            'phone_number' => ['required', 'string', 'min:8', 'max:30'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $phoneNumber = $validated['phone_number'];

        if (! $phoneNumber) {
            return back()->withErrors([
                'phone_number' => 'Isi nomor HP pelanggan terlebih dahulu sebelum membuat akun.',
            ])->withInput();
        }

        $phoneNumber = trim($phoneNumber);

        if (User::where('phone_number', $phoneNumber)->exists()) {
            return back()->withErrors([
                'phone_number' => 'Nomor HP sudah digunakan oleh akun lain.',
            ])->withInput();
        }

        $password = $validated['password'] ?: Str::random(12);

        $newUser = User::create([
            'name' => $customer->name,
            'email' => $this->generateCustomerEmail($customer),
            'phone_number' => $phoneNumber,
            'password' => $password,
            'role' => User::ROLE_USER,
            'status' => User::STATUS_APPROVED,
            'area_id' => $customer->area_id,
            'customer_id' => $customer->id,
            'area' => optional($customer->area)->name,
            'address_short' => $customer->address_short,
            'approved_at' => now(),
            'must_update_password' => true,
        ]);

        if ($customer->phone_number !== $phoneNumber) {
            $customer->update(['phone_number' => $phoneNumber]);
        }

        ChangeLog::record($request->user(), 'customer.create-account', 'Membuat akun login pelanggan.', [
            'subject_type' => Customer::class,
            'subject_id' => $customer->id,
            'after' => [
                'user_id' => $newUser->id,
                'phone_number' => $newUser->phone_number,
            ],
        ]);

        return redirect()
            ->route('menu.customers.show', $customer)
            ->with('status', 'Akun pelanggan berhasil dibuat.')
            ->with('generated_credentials', [
                'phone_number' => $newUser->phone_number,
                'password' => $password,
            ]);
    }

    private function authorizeAccess(?User $user, ?Customer $customer = null): void
    {
        abort_if(! $user || (! $user->isAdmin() && ! $user->isPetugas()), 403);

        if ($customer && $user->isPetugas()) {
            $allowedAreaIds = $user->assignedAreas()->pluck('areas.id')->toArray();
            if (! in_array($customer->area_id, $allowedAreaIds, true) && $user->area_id !== $customer->area_id) {
                abort(403, 'Pelanggan tidak berada pada area Anda.');
            }
        }
    }

    private function availableAreas(User $user)
    {
        // Admin can access all areas
        if ($user->isAdmin()) {
            return Area::orderBy('name')->get();
        }

        // For petugas, get areas from direct assignment OR pivot table
        $areaIds = [];
        
        // Check pivot table if exists
        try {
            $areaIds = $user->assignedAreas()->pluck('areas.id')->toArray();
        } catch (\Exception $e) {
            // Pivot table might not exist, ignore
        }
        
        // Also include directly assigned area
        if ($user->area_id) {
            $areaIds[] = $user->area_id;
        }

        // If no areas assigned, return all (fallback for petugas without assignment)
        if (empty($areaIds)) {
            return Area::orderBy('name')->get();
        }

        return Area::whereIn('id', $areaIds)->orderBy('name')->get();
    }

    private function updateAreaCounters(?Area $area): void
    {
        if (! $area) {
            return;
        }

        $area->update(['customer_count' => $area->customers()->count()]);
    }

    private function buildStats($customers, $areas, User $user): array
    {
        $total = $customers->count();
        $areaTotals = $areas->mapWithKeys(function ($area) use ($customers) {
            return [$area->id => $customers->where('area_id', $area->id)->count()];
        });

        if ($user->isPetugas()) {
            $assignedArea = $user->assignedAreas()->pluck('areas.id');
            $areaTotals = $areaTotals->only($assignedArea->all() ?: [$user->area_id]);
        }

        return [
            'total' => $total,
            'areaTotals' => $areaTotals,
        ];
    }

    private function generateCustomerCode(): string
    {
        do {
            $code = str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Customer::where('customer_code', $code)->exists());

        return $code;
    }

    private function generateCustomerEmail(Customer $customer): string
    {
        $base = Str::slug($customer->name ?: 'pelanggan');
        $base = $base !== '' ? $base : 'pelanggan';
        $sequence = 0;

        do {
            $localPart = $base . '.' . $customer->customer_code;

            if ($sequence > 0) {
                $localPart .= '.' . $sequence;
            }

            $email = $localPart . '@customers.meterpams';
            $sequence++;
        } while (User::where('email', $email)->exists());

        return $email;
    }
}
