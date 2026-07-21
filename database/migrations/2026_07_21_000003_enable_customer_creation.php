<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE property_customers DROP FOREIGN KEY fk_pc_customer');
        DB::statement('ALTER TABLE customers MODIFY id BIGINT NOT NULL AUTO_INCREMENT');
        DB::statement('ALTER TABLE property_customers MODIFY link_id BIGINT NOT NULL AUTO_INCREMENT');
        DB::statement('ALTER TABLE property_customers ADD CONSTRAINT fk_pc_customer FOREIGN KEY (customer_id) REFERENCES customers (id)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE property_customers DROP FOREIGN KEY fk_pc_customer');
        DB::statement('ALTER TABLE property_customers MODIFY link_id BIGINT NOT NULL');
        DB::statement('ALTER TABLE customers MODIFY id BIGINT NOT NULL');
        DB::statement('ALTER TABLE property_customers ADD CONSTRAINT fk_pc_customer FOREIGN KEY (customer_id) REFERENCES customers (id)');
    }
};
