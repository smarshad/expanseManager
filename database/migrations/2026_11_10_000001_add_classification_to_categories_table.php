<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Category -> expense classification mapping. NULL means "inherit from parent
 * category" (and finally the system default).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('transaction_kind', 20)->nullable()->after('nature');
            $table->string('spending_nature', 20)->nullable()->after('transaction_kind');
            $table->string('recurrence', 20)->nullable()->after('spending_nature');
        });

        // Initial mapping, editable later from the UI. [kind, nature, recurrence]
        $seed = [
            // parent level defaults
            'groceries' => [null, 'essential', null],
            'food & dining' => [null, 'essential', null],
            'household' => [null, 'essential', null],
            'medical' => [null, 'essential', null],
            'travel' => [null, 'essential', null],
            'utilities' => [null, 'essential', null],
            'education' => [null, 'essential', null],
            'insurance' => [null, 'essential', null],
            'vehicle' => [null, 'essential', null],
            'shopping' => [null, 'discretionary', null],
            'entertainment' => [null, 'discretionary', null],
            'personal care' => [null, 'discretionary', null],
            'donation' => [null, 'discretionary', null],
            'subcription' => [null, 'discretionary', 'monthly'],
            'investment' => ['investment', null, null],
            'lending & borrowing' => ['debt', null, null],
            'cash withdrawal' => ['transfer', null, null],
            'cash flow' => ['transfer', null, null],
            'encash' => ['transfer', null, null],
            // child overrides
            'flight' => [null, 'discretionary', null],
            'restaurant' => [null, 'discretionary', null],
            'juice' => [null, 'discretionary', null],
            'juice / drinks' => [null, 'discretionary', null],
            'softdrinks' => [null, 'discretionary', null],
            'ice cream' => [null, 'discretionary', null],
            'sweets' => [null, 'discretionary', null],
            'little joys' => [null, 'discretionary', null],
            'chocolate' => [null, 'discretionary', null],
            'coconut water' => [null, 'discretionary', null],
            'maid salary' => [null, 'essential', 'monthly'],
            'internet' => [null, 'essential', 'monthly'],
            'sip' => ['investment', null, 'monthly'],
            // names carried over from the old hardcoded lists
            'ipo' => ['investment', null, null],
            'mutual funds' => ['investment', null, 'monthly'],
            'stocks' => ['investment', null, null],
            'credit card bill' => ['transfer', null, 'monthly'],
            'withdrawal' => ['transfer', null, null],
            'return borrow' => ['debt', null, null],
            'educate child' => [null, 'essential', 'monthly'],
            'school fees' => [null, 'essential', 'monthly'],
            'gas' => [null, 'essential', null],
            'gas cylinder' => [null, 'essential', null],
            'grocery' => [null, 'essential', null],
            'light bill' => [null, 'essential', 'monthly'],
            'maid' => [null, 'essential', 'monthly'],
            'salma/maid/home' => [null, 'essential', 'monthly'],
            'medical group' => [null, 'essential', 'monthly'],
            'medicine' => [null, 'essential', null],
            'milk' => [null, 'essential', 'daily'],
            'mobile recharge' => [null, 'essential', 'monthly'],
            'recharge' => [null, 'essential', 'monthly'],
            'petrol' => [null, 'essential', null],
            'rent' => [null, 'essential', 'monthly'],
            'vegetables' => [null, 'essential', null],
            'water' => [null, 'essential', null],
            'saloon' => [null, 'discretionary', null],
            'snacks' => [null, 'discretionary', null],
            'tea' => [null, 'discretionary', 'daily'],
            'travelling' => [null, null, 'daily'],
        ];

        foreach ($seed as $name => [$kind, $nature, $recurrence]) {
            $values = array_filter([
                'transaction_kind' => $kind,
                'spending_nature' => $nature,
                'recurrence' => $recurrence,
            ], fn ($v) => $v !== null);

            DB::table('categories')->whereRaw('LOWER(name) = ?', [$name])->update($values);
        }
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['transaction_kind', 'spending_nature', 'recurrence']);
        });
    }
};
