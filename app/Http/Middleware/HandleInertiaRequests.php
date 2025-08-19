<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // 🔹 Monta o usuário autenticado ou mock
        $user = $request->user()
            ? [
                'id'                => $request->user()->id,
                'name'              => $request->user()->name,
                'username'          => $request->user()->username ?? null,
                'email'             => $request->user()->email,
                'avatar'            => $request->user()->avatar ?? null,
                'bio'               => $request->user()->bio ?? null,
                'role'              => $request->user()->getRoleNames()->first() ?? 'user',
                'plan'              => $request->user()->plan ?? 'free',
                'email_verified_at' => $request->user()->email_verified_at,
                'is_mock'           => false,
            ]
            : [
                'id'                => 1,
                'name'              => 'João Dev',
                'username'          => 'joaodev',
                'email'             => 'joao@example.com',
                'avatar'            => 'https://ui-avatars.com/api/?name=Joao+Dev&background=random&color=fff',
                'bio'               => 'Desenvolvedor apaixonado por código.',
                'role'              => 'user',
                'plan'              => 'free',
                'email_verified_at' => now()->toDateTimeString(),
                'is_mock'           => true,
            ];

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user,
            ],
            'ziggy' => fn() => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            // 🔹 Mensagens de sessão para toasts ou alerts
            'flash' => [
                'success' => $request->session()->get('success'),
                'error'   => $request->session()->get('error'),
                'info'    => $request->session()->get('info'),
            ],
            // 🔹 Erros de validação
            'errors' => function () use ($request) {
                return $request->session()->get('errors')
                    ? $request->session()->get('errors')->getBag('default')->getMessages()
                    : (object)[];
            },
        ]);
    }
}
