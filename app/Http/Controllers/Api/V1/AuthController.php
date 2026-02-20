<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterUserRequest;
use App\Models\Area;
use App\Models\ChangeLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'phone_number' => ['required', 'string'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ]);

        $user = User::query()
            ->where('phone_number', $credentials['phone_number'])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Nomor HP atau kata sandi tidak valid.',
            ], 422);
        }

        if (! $user->isApproved()) {
            return response()->json([
                'message' => 'Akun Anda belum disetujui admin. Silakan tunggu konfirmasi.',
            ], 403);
        }

        $token = $user->createToken($credentials['device_name'] ?? 'flutter-android')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => $this->transformUser($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }

    public function register(RegisterUserRequest $request): JsonResponse
    {
        $avatarPath = $request->file('avatar')?->store('avatars', 'public');
        $area = Area::findOrFail($request->integer('area_id'));

        $phoneNumber = $request->string('phone_number')->trim()->toString();

        $user = User::create([
            'name' => $request->string('name')->trim()->toString(),
            'email' => $this->generateEmailFromPhone($phoneNumber),
            'phone_number' => $phoneNumber,
            'password' => $request->string('password')->toString(),
            'role' => User::ROLE_USER,
            'status' => User::STATUS_PENDING,
            'area_id' => $area->id,
            'area' => $area->name,
            'address_short' => $request->filled('address_short') ? $request->string('address_short')->trim()->toString() : null,
            'avatar_path' => $avatarPath,
            'approved_at' => null,
        ]);

        ChangeLog::record($user, 'user.register', 'Pengguna mendaftar akun.', [
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'after' => $user->only(['id', 'name', 'phone_number', 'status', 'area_id']),
        ]);

        return response()->json([
            'message' => 'Registrasi berhasil. Akun Anda menunggu persetujuan admin.',
            'user' => $this->transformUser($user),
        ], 201);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => $this->transformUser($user),
        ]);
    }

    public function forceUpdatePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->must_update_password) {
            return response()->json([
                'message' => 'Kata sandi tidak perlu diperbarui.',
            ], 422);
        }

        $data = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user->forceFill([
            'password' => $data['password'],
            'must_update_password' => false,
        ])->save();

        return response()->json([
            'message' => 'Kata sandi berhasil diperbarui.',
            'user' => $this->transformUser($user->fresh()),
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address_short' => ['nullable', 'string', 'max:500'],
        ]);

        $user->update($data);

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'user' => $this->transformUser($user->fresh()),
        ]);
    }

    public function uploadAvatar(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'avatar' => ['required', 'image', 'max:4096'],
        ]);

        if ($user->avatar_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar_path);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar_path' => $path]);

        return response()->json([
            'message' => 'Avatar berhasil diperbarui.',
            'avatar_url' => asset('storage/' . $path),
            'user' => $this->transformUser($user->fresh()),
        ]);
    }

    private function generateEmailFromPhone(string $phoneNumber): string
    {
        $digits = preg_replace('/\D+/', '', $phoneNumber) ?: Str::random(12);

        return "{$digits}@user.local";
    }

    private function transformUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'phone_number' => $user->phone_number,
            'role' => $user->role,
            'status' => $user->status,
            'is_approved' => $user->isApproved(),
            'must_update_password' => (bool) $user->must_update_password,
            'area_id' => $user->area_id,
            'area' => $user->area,
            'customer_id' => $user->customer_id,
            'address_short' => $user->address_short,
            'avatar_url' => $user->avatar_path ? asset('storage/'.$user->avatar_path) : null,
        ];
    }
}
