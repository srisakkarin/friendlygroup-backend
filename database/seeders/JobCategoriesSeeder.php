<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JobCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $jobCategories = [
            [
                'name' => 'การตลาดและการขาย',
                'description' => 'งานที่เกี่ยวข้องกับการตลาด การขาย และการส่งเสริมการขาย',
                'image' => '' // Replace with actual image path
            ],
            [
                'name' => 'เทคโนโลยีสารสนเทศ',
                'description' => 'งานที่เกี่ยวข้องกับคอมพิวเตอร์ ซอฟต์แวร์ และเทคโนโลยีสารสนเทศ',
                'image' => '' // Replace with actual image path
            ],
            [
                'name' => 'การเงินและการบัญชี',
                'description' => 'งานที่เกี่ยวข้องกับการเงิน การบัญชี และการธนาคาร',
                'image' => '' // Replace with actual image path
            ],
            [
                'name' => 'วิศวกรรม',
                'description' => 'งานที่เกี่ยวข้องกับวิศวกรรมศาสตร์แขนงต่างๆ',
                'image' => '' // Replace with actual image path
            ],
            [
                'name' => 'การออกแบบและศิลปะ',
                'description' => 'งานที่เกี่ยวข้องกับการออกแบบ กราฟิก และศิลปะ',
                'image' => '' // Replace with actual image path
            ],
            [
                'name' => 'การศึกษาและการฝึกอบรม',
                'description' => 'งานที่เกี่ยวข้องกับการศึกษา การสอน และการฝึกอบรม',
                'image' => '' // Replace with actual image path
            ],
            [
                'name' => 'บริการลูกค้า',
                'description' => 'งานที่เกี่ยวข้องกับการบริการลูกค้าและการสนับสนุน',
                'image' => '' // Replace with actual image path
            ],
            [
                'name' => 'ทรัพยากรบุคคล',
                'description' => 'งานที่เกี่ยวข้องกับการบริหารทรัพยากรบุคคล',
                'image' => '' // Replace with actual image path
            ],
            [
                'name' => 'การแพทย์และสุขภาพ',
                'description' => 'งานที่เกี่ยวข้องกับการแพทย์ การพยาบาล และสุขภาพ',
                'image' => '' // Replace with actual image path
            ],
            [
                'name' => 'การผลิตและอุตสาหกรรม',
                'description' => 'งานที่เกี่ยวข้องกับการผลิตและอุตสาหกรรม',
                'image' => '' // Replace with actual image path
            ],
        ];
         // Insert the data into the job_categories table
         DB::table('job_categories')->insert($jobCategories);
    }
}
