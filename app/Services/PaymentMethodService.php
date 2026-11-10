<?php

namespace App\Services;

use App\DTOs\PaymentMethodDO;
use App\Interfaces\PaymentMethodRepositoryInterface;
use App\Models\PaymentMethod;
use DomainException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class PaymentMethodService
{

    // Define a constant for the cache key
    protected const CACHE_KEY_ALL = 'payment_method.all';

    public function __construct(protected PaymentMethodRepositoryInterface $paymentMethodRepository) {}

    /**
     * Retrieves paginatedpayment methods.
     */
    public function getPaginated(User $user, ?int $perPage = null, ?string $search = null): LengthAwarePaginator
    {

        $userId = null; // Default: view all
        // Check for 'view all' permission
        if ($user->can('paymentmethod.view.all')) {
            $userId = null; // No filtering needed
        } elseif ($user->can('paymentmethod.view.own')) {
            // If only 'view own' is granted, filter by the user's ID
            $userId = $user->id;
        }
        // Caching is typically not used for paginated results unless the query is very expensive and static.
        return $this->paymentMethodRepository->getPaginated($perPage, ['*'], $userId, $search);
    }

    /**
     * Create New Record
     */

    public function create(PaymentMethodDO $paymentMethodDO): PaymentMethod
    {

        return DB::transaction(function () use ($paymentMethodDO) {
            $paymentMethod = $this->paymentMethodRepository->create($paymentMethodDO->toArray());

            // Invalidate cache
            Cache::forget(self::CACHE_KEY_ALL);
            return $paymentMethod;
        });
    }

    /**
     * Updates an existing payment method.
     */

    public function update(int $id, PaymentMethodDO $paymentMethodDO): PaymentMethod
    {

        return DB::transaction(function () use ($id, $paymentMethodDO) {
            // 1 core persistence
            $updateData = $this->paymentMethodRepository->update($id, $paymentMethodDO->toArray());

            // 2. Invalidate the cache for the 'all' list
            Cache::forget(self::CACHE_KEY_ALL);

            return $updateData;
        });
    }

    /**
     * Deletes payment method after validation.
     * @throws CannotDeleteExpenseTypeException
     */

    public function delete(int $id): bool
    {
        $this->validatePaymentMethodCanBeDeleted($id);

        // Perform deletion


        $delete = $this->paymentMethodRepository->delete($id);

        if (!$delete) {
            throw new DomainException('The repository failed to delete the payment method.');
        }

        // Invalidate the cache for 'all' list
        Cache::forget(self::CACHE_KEY_ALL);

        return true;
    }

    /**
     * Business rule: Check if the payment method can be deleted.
     * @throws DomainException
     */
    private function validatePaymentMethodCanBeDeleted(int $id): void
    {
        if ($this->paymentMethodRepository->hasRelatedExpenses($id)) {
            // Use DomainException with a specific message for the controller to handle
            throw new DomainException('Cannot deletepayment method. Related expense records exist.');
        }
    }
}
