<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('emergency_requests', function (Blueprint $table) {
            $table->id();
            
            // 1. ربط الطلب بالمستخدم اللي داس على زرار الطوارئ
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // 2. ربط الطلب بالمستشفى (ممكن تكون null في الأول لحد ما السيستم يختار الأقرب)
            $table->foreignId('hospital_id')->nullable()->constrained()->onDelete('set null');

            // 3. إحداثيات المريض (عشان تظهر للمسعف على الخريطة)
            $table->decimal('user_lat', 10, 8);
            $table->decimal('user_lng', 11, 8);

            // 4. حالة الطلب (pending, accepted, on_the_way, completed)
            $table->string('status')->default('pending');

            // 5. أي ملاحظات سريعة ممكن يبعتها المريض
            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emergency_requests');
    }
};