<?php

namespace App\Http\Controllers;

use App\DTOs\PaymentMethodDO;
use App\Http\Resources\PaymentMethodResource;
use Illuminate\Http\Request;
use App\Services\PaymentMethodService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\PaymentMethod\StoreRequest;
use App\Http\Requests\PaymentMethod\UpdateRequest;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

use DomainException;
use Exception;

class PaymentMethodController extends BaseController
{
    public function __construct(protected PaymentMethodService $service) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.paymentmethod.index');
    }

    public function getAll(Request $request): JsonResponse
    {

        // 1. Authorize the action using the PaymentMethodPolicy
        // This will throw an AuthorizationException (403 Forbidden) if the user is not authorized
        // to view any categories (i.e., viewAny returns false).
        $this->authorize('viewAny', PaymentMethod::class);

        // 2. Get the authenticated user.
        $user = Auth::user();

        // Safety check: If the route is not protected by 'auth' middleware, $user could be null.
        // We ensure the user is an instance of the User model before passing it to the service.
        if (!$user instanceof User) {
            // If the user is not authenticated, we throw an exception.
            // In a real Laravel app, the 'auth' middleware should handle this,
            // but this check adds robustness.
            abort(401, 'Unauthenticated.');
        }
        $perPage            = $request->get('per_page', NULL);
        $draw               = $request->get('draw', 1);
        $search             = $request->get('search');
        $data               = $this->service->getPaginated($user, $perPage, $search);
        $response           = PaymentMethodResource::collection($data)->response()->getData(true);
        $response['draw']   = (int) $draw;
        $response['recordsTotal'] = $response['meta']['total'];
        $response['recordsFiltered'] = $response['meta']['total'];
        return response()->json($response);
    }

    public function create()
    {
        $this->authorize('create', PaymentMethod::class);

        return view('admin.paymentmethod._form', ['paymentmethod' => null]);
    }

    /**
     * Store a newly created record.
     */
    public function store(StoreRequest $request)
    {
        try {
            $this->authorize('create', PaymentMethod::class);

            // Log raw incoming data
            $this->logInfo('Raw data for new payment method', $request->all());

            // Validate and prepare DTO
            $dto = $this->createDTOFromRequest($request);

            // Service layer creation
            $data  = $this->service->create($dto);

            return $this->successResponse(new PaymentMethodResource($data), 'Payment Method Succesfully Created', 201, ['callback' => route('expensetype.list')]);
        } catch (ValidationException $e) {
            return $this->handleValidationException($e);
        } catch (DomainException $e) {
            return $this->handleDomainException($e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function edit(PaymentMethod $paymentMethod)
    {
        $this->authorize('update', $paymentMethod);

        // $PaymentMethod = PaymentMethod::findOrFail($id);
        $this->logInfo('edit', ['PaymentMethod' => $paymentMethod->id]);
        return view('admin.paymentmethod._form', ['paymentmethod' => $paymentMethod]);
    }

    /**
     * update the specified resource in storage
     */

    public function update(UpdateRequest $request, PaymentMethod $paymentMethod)
    {
        try {
            $this->authorize('update', $paymentMethod);

            $this->logInfo('Raw data for update Payment method', $request->all());

            // valiadate data and make DTO
            $dto = $this->createDTOFromRequest($request);

            $udapteData = $this->service->update($paymentMethod->id, $dto);

            return $this->successResponse(
                $udapteData,
                'Payment method successfully updated.',
                200,
                ['callback' => route('paymentmethod.list')]
            );
        } catch (ValidationException $e) {
            $this->handleValidationException($e);
        } catch (DomainException $e) {
            $this->handleDomainException($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */

    public function destroy(PaymentMethod $paymentMethod)
    {
        try {
            $this->authorize('delete', $paymentMethod);

            // Log raw incoming data
            $this->logInfo('Raw request data for delete payment method');

            $delete = $this->service->delete($paymentMethod->id);

            if ($delete) {
                return $this->successResponse(NULL, 'Payment Method Deleted Successfully');
            } else {
                return $this->errorResponse('Some Record exist with this payment type', 409);
            }
        } catch (ValidationException $e) {
            $this->handleValidationException($e);
        } catch (DomainException $e) {
            $this->handleDomainException($e);
        }
    }

    /**
     * Helper to create DTO from request.
     */
    private function createDTOFromRequest($request): PaymentMethodDO
    {

        $validatedData = $request->validated();

        $validatedData['created_by'] = $request->user()->id ?? NULL;

        $this->logInfo('Passing Validated Data to DTO For New Payment Method', $validatedData);

        $dto = PaymentMethodDO::fromArray($validatedData);

        $this->logInfo('DTO created For New Payment Method', $dto->toArray());

        return $dto;
    }
}
