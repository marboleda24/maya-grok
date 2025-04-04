<?php

namespace App\Policies;

use App\Models\Portfolio;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PortfolioPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Portfolio $portfolio)
    {
        return $user->id === $portfolio->user_id;
    }

    public function update(User $user, Portfolio $portfolio)
    {
        \Log::info('User ID: ' . $user->id . ' | Portfolio User ID: ' . $portfolio->user_id);
        return $user->id === $portfolio->user_id;
    }

    public function delete(User $user, Portfolio $portfolio)
    {
        \Log::info('Intentando eliminar - User ID: ' . $user->id . ' | Portfolio ID: ' . $portfolio->id . ' | Portfolio User ID: ' . ($portfolio->user_id ?? 'null'));
        return $user->id === $portfolio->user_id;
    }
}