<?php

namespace App\Controllers;

use App\Libraries\PasswordHasher;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class SetupController extends BaseController
{
    public function index()
    {
        $database = db_connect();
        if ($database->tableExists('users') && (new UserModel())->countAllResults() > 0) {
            return view('auth/setup-admin', [
                'setupEnabled' => false,
                'error'        => 'Administrator awal sudah dibuat. Silakan masuk melalui halaman login.',
            ]);
        }

        $setupKey = (string) env('app.setupKey', '');
        if (strlen($setupKey) < 32) {
            return view('auth/setup-admin', [
                'setupEnabled' => false,
                'error'        => 'Kunci pengaturan belum tersedia atau terlalu pendek. Atur kunci acak minimal 32 karakter di file .env.',
            ]);
        }

        $users = $this->users();
        if ($users->countAllResults() > 0) {
            return view('auth/setup-admin', [
                'setupEnabled' => false,
                'error'        => 'Administrator awal sudah dibuat. Silakan masuk melalui halaman login.',
            ]);
        }

        helper('form');

        return view('auth/setup-admin', [
            'setupEnabled' => true,
            'error'        => null,
        ]);
    }

    public function create(): ResponseInterface|string
    {
        $setupKey = (string) env('app.setupKey', '');
        $submittedKey = (string) $this->request->getPost('setup_key');
        if (strlen($setupKey) < 32 || ! hash_equals($setupKey, $submittedKey)) {
            return $this->response->setStatusCode(403)->setBody('Initial setup is not authorized.');
        }

        $users = $this->users();
        if ($users->countAllResults() > 0) {
            return redirect()->to('/login')->with('error', 'Administrator awal sudah dibuat. Silakan masuk.');
        }

        $username = strtolower(trim((string) $this->request->getPost('username')));
        $name = trim((string) $this->request->getPost('name'));
        $password = (string) $this->request->getPost('password');

        if (preg_match('/\A[a-zA-Z0-9_.-]{3,50}\z/', $username) !== 1) {
            return $this->showSetupError('Username harus 3-50 karakter dan hanya berisi huruf, angka, titik, garis bawah, atau tanda hubung.');
        }
        if ($name === '' || mb_strlen($name) > 100) {
            return $this->showSetupError('Nama wajib diisi dan maksimal 100 karakter.');
        }
        if (mb_strlen($password) < 12 || mb_strlen($password) > 255) {
            return $this->showSetupError('Password harus 12-255 karakter.');
        }

        $credentials = PasswordHasher::hash($password);
        $userId = $users->insert([
            'name'          => $name,
            'username'      => $username,
            'password_hash' => $credentials['hash'],
            'password_salt' => $credentials['salt'],
            'role'          => 'admin',
        ]);
        if ($userId === false) {
            return $this->showSetupError('Administrator tidak dapat dibuat. Pastikan username belum digunakan dan coba lagi.');
        }

        session()->regenerate(true);
        session()->set([
            'user_id'  => (int) $userId,
            'name'     => $name,
            'username' => $username,
            'role'     => 'admin',
        ]);

        return redirect()->to('/admin/users');
    }

    private function users(): UserModel
    {
        $database = db_connect();
        if (! $database->tableExists('users')) {
            service('migrations')->latest();
        }
        if (! $database->tableExists('users')) {
            throw new \RuntimeException('The initial administrator migration did not create the users table.');
        }

        return new UserModel();
    }

    private function showSetupError(string $error): string
    {
        helper('form');

        return view('auth/setup-admin', [
            'setupEnabled' => true,
            'error'        => $error,
        ]);
    }
}
