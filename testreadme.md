##step 1
    php artisan make:model Expense -mc
    1.1. Model, Migration, and Controller

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        public function up(): void
        {
            Schema::create('expenses', function (Blueprint $table) {
                $table->id();
                $table->string('description', 500);
                $table->decimal('amount', 10, 2);
                $table->foreignId('category_id')->constrained('categories');
                $table->foreignId('expense_type_id')->constrained('expense_types');
                $table->foreignId('payment_method_id')->constrained('payment_methods');
                $table->string('file_path', 500)->nullable();
                $table->text('cashback')->nullable();
                $table->text('notes')->nullable();
                $table->date('expense_date');
                $table->foreignId('created_by')->constrained('users');
                $table->timestamps();
                $table->softDeletes();
                
                $table->index('category_id');
                $table->index('expense_type_id');
                $table->index('payment_method_id');
                $table->index('created_by');
                $table->index('expense_date');
            });
        }
        // ...
    };

##step 2 php artisan migrate

##step 3 Add routes

##step 4 Update controller add method

##step 5 Update model add fillable property

##step 6 Create language file

##step 7 Create UI

##step 8 Create JS File

we write steps for create/store

    1. Add link in menu
    2. create create form
    3. check route expense.creaete
    4. Create DTO 'ExpenseDTO'
        create two method fromArray, toArray
    5. Create request 'expense\StoreRequest' for server side validation
    6. Create expenseInterface php artisan make:interface ExpenseRepositoryInterface

        interface ExpenseRepositoryInterface
        {
            /**
            * Create a new record.
            */
            public function create(array $details): Expense;

        }
    7. touch app/Repositories/PaymentMethodRepository.php to implement 6



