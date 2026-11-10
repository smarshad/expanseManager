# Day1 Basic setup for RBAC create Permission(CRUD mixed normal/ajax)

1st install laravel spatie package
    composer require spatie/laravel-permission
    publish php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
    php artisan config:clear   
            or
    php artisan optimize:clear

    php artisan migrate

2nd create view -- admin/permissions [index,create,edit,show]
3rd add in app.layout <meta name="csrf-token" content="{{ csrf_token() }}"> for ajax
4th create PermissionController, alter permision and role table add description column
5th create request for StorePermissionRequest
6th create request for UpdatePermissionRequest
7th create BaseFormRequest
    for to create failedValidation in case of ajax  
    protected function failedValidation(Validator $validator)
    {
        // If the request expects JSON (AJAX, fetch(), axios)
        if ($this->expectsJson()) {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors'  => $validator->errors(),
                ], 422)
            );
        }

        // Otherwise use Laravel's normal behavior (redirect back with errors)
        parent::failedValidation($validator);
    }   

# Day2 
    Refactor Permisison controller centralised log data
    Add one column into permission table (module)
    drop unique([name,guard_name]) and add('name','module', 'guard_name')
    Change Validation store and update permission request
    Create Permission model and extend with spatie permission model
    change config/permission.php change model to our newly created model
    clear cache
    Delete Using Ajax
    New function in helper for log
    Create Role Controller (resource)
    Create Views for role
    add route for roles
    add menu in left
    change in common ajax-form-submit.js if (field === 'permissions') {$('.alert-danger').html(messages[0]).show();}
    complete CRUD Role module using AJAX

# Day3 and 4
# Granular Step-by-Step Guide to Implementing CRUD Functionality

## Introduction: The Layered Approach

This guide provides a highly detailed, action-oriented sequence for implementing **CRUD** (Create, Read, Update, Delete) functionality in a modern, layered application architecture (e.g., Laravel with Service/Repository patterns).

The core sequence remains **Model → Controller → Views**, but we will focus on the granular steps within the **Controller** where advanced components like **Request**, **DTO**, **Service**, and **Resource** are integrated.

---

## Phase 1: Foundational Setup (Model, Service, DTO, Resource)

Before writing the Controller methods, the foundational components must be in place.

| Component | Purpose | Key Task |
| :--- | :--- | :--- |
| **Model** | Defines the database structure and relationships. | Create the `User` Model and its migration. |
| **Service** | Encapsulates all business logic and transaction management. | Create the `UserService` with methods like `create()`, `update()`, and `deleteById()`. |
| **DTO** | Provides a type-safe contract for data transfer. | Create the `UserDTO` to structure input data passed to the Service. |
| **Request** | Handles request validation and authorization. | Create `StoreUserRequest` and `UpdateUserRequest` for input validation. |
| **Resource** | Transforms Model data into a standardized API response format. | Create `UserResource` to shape the output data. |

---

## Phase 2: Controller Implementation (The Granular Steps)

The **Controller** is the orchestrator, delegating tasks to the other components. The implementation is structured around the standard RESTful methods.

### 1. Read (List): `index()`

**Goal:** Fetch a list of records and display them in a view.

| Step | Action | Component Used | Output |
| :--- | :--- | :--- | :--- |
| **1** | Fetch paginated data. | `Model` (e.g., `User::orderBy()->paginate()`) | `$users` (Collection of Models) |
| **2** | Pass data to the view. | `Controller` | `return view('admin.users.index', compact('users'))` |

### 2. Create (Form): `create()`

**Goal:** Display the form for creating a new record.

| Step | Action | Component Used | Output |
| :--- | :--- | :--- | :--- |
| **1** | Fetch necessary supporting data (e.g., roles, categories). | `Model` (e.g., `Role::all()`) | `$roles` (Collection of Models) |
| **2** | Pass data to the view. | `Controller` | `return view('admin.users.create', compact('roles'))` |

### 3. Create (Save): `store(StoreUserRequest $request)`

**Goal:** Validate input, persist the new record, and return a success response. **This is the most complex step.**

| Step | Action | Component Used | Role |
| :--- | :--- | :--- | :--- |
| **1** | **Request Validation.** | `StoreUserRequest` | Automatically validates and authorizes the incoming HTTP request data. |
| **2** | **Extract Validated Data.** | `Controller` | `$validatedData = $request->validated()` |
| **3** | **Create DTO.** | `UserDTO` | `$dto = UserDTO::fromArray($validatedData)`: Structures the data for the Service layer. |
| **4** | **Begin Transaction.** | `DB` | `DB::beginTransaction()`: Ensures atomicity for multiple database operations. |
| **5** | **Delegate Business Logic.** | `UserService` | `$user = $this->service->create($dto)`: The Service handles the actual Model creation and any related logic. |
| **6** | **Commit Transaction.** | `DB` | `DB::commit()`: Finalizes the database changes. |
| **7** | **Format Response.** | `UserResource` | `new UserResource($user)`: Transforms the created Model into a clean JSON structure. |
| **8** | **Return Response.** | `Controller` | `return response()->json([...])`: Sends the final success response to the client. |

### 4. Update (Form): `edit($id)`

**Goal:** Fetch an existing record and display its data in an edit form.

| Step | Action | Component Used | Output |
| :--- | :--- | :--- | :--- |
| **1** | Fetch the record to be edited. | `Model` (e.g., `User::findOrFail($id)`) | `$user` (Single Model) |
| **2** | Fetch necessary supporting data (e.g., roles). | `Model` (e.g., `Role::all()`) | `$roles` (Collection of Models) |
| **3** | Pass data to the view. | `Controller` | `return view('admin.users.edit', compact('user', 'roles'))` |

### 5. Update (Save): `update(UpdateUserRequest $request, User $user)`

**Goal:** Validate input, update the existing record, and return a success response.

| Step | Action | Component Used | Role |
| :--- | :--- | :--- | :--- |
| **1** | **Request Validation.** | `UpdateUserRequest` | Validates and authorizes the request data. |
| **2** | **Extract Validated Data.** | `Controller` | `$validatedData = $request->validated()` |
| **3** | **Create DTO.** | `UserDTO` | `$dto = UserDTO::fromArray($validatedData)`: Structures the update data. |
| **4** | **Begin Transaction.** | `DB` | `DB::beginTransaction()` |
| **5** | **Delegate Business Logic.** | `UserService` | `$user = $this->service->update($user, $dto)`: The Service handles the Model update. |
| **6** | **Commit Transaction.** | `DB` | `DB::commit()` |
| **7** | **Format Response.** | `UserResource` | `new UserResource($user)`: Transforms the updated Model. |
| **8** | **Return Response.** | `Controller` | `return response()->json([...])`: Sends the final success response. |

### 6. Delete: `destroy(Request $request)`

**Goal:** Delete a record and return a success response.

| Step | Action | Component Used | Role |
| :--- | :--- | :--- | :--- |
| **1** | **Identify Record.** | `Request` | Extracts the ID of the record to be deleted (e.g., `$request->id`). |
| **2** | **Delegate Deletion.** | `UserService` | `$this->service->deleteById((int) $request->id)`: The Service handles the deletion logic and error handling. |
| **3** | **Return Response.** | `Controller` | `return response()->json([...])`: Sends the final success response. |

---

## Phase 3: Views (The Presentation)

The Views are the final consumer of the data prepared by the Controller.

| View | Purpose | Controller Method | Data Dependency |
| :--- | :--- | :--- | :--- |
| `index.blade.php` | Displays the list of records. | `index()` | `$users` (Collection of Models) |
| `create.blade.php` | Displays the form for creating a new record. | `create()` | `$roles` (Supporting data) |
| `edit.blade.php` | Displays the form for updating an existing record. | `edit()` | `$user` (Single Model), `$roles` (Supporting data) |

## Phase 4: new Migration for(login history and show last login)
    1. add two field in users table last_login_at and current_login_at
    2. create new table login_histories (id,user_id,login_at,ip_address,user_agent,created_at,updated_at)

## Phase 5: event listner for update last_login_at and current_login_at
    php artisan make:listener LogUserLogin --event=Illuminate\Auth\Events\Login
    `app/Listeners/LogUserLogin.php`
    public function handle(Login $event): void
    {
        $user = $event->user;

        // Move current login to last login
        $user->last_login_at = $user->current_login_at;

        // Set new login time
        $user->current_login_at = now();

        $user->save();

        // Save login history
        LoginHistory::create([
            'user_id'    => $user->id,
            'login_at'   => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

## Phase 6: create new UI for assigning permision to user
    create new controller UserPermissionController
    create new request UserPermissionRequest
    add new function in UserService
        assignPermissions
    Also at the time of assigning permission to user checked existing permission via direct permission assign or by role
        $userDirectPermissions = $user->getDirectPermissions()->pluck('id')->toArray();
        $userRolePermissions   = $user->getPermissionsViaRoles()->pluck('id')->toArray();

