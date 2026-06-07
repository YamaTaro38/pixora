<?php
// database/migrations/2024_01_01_000013_update_testimonials_setting.php

use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

return new class extends Migration
{
    public function up()
    {
        $testimonials = json_decode(Setting::get('testimonials', '[]'), true);
        foreach ($testimonials as &$testimonial) {
            if (!isset($testimonial['photo'])) {
                $testimonial['photo'] = null;
            }
        }
        Setting::set('testimonials', json_encode($testimonials));
    }

    public function down()
    {
        // No need to rollback
    }
};