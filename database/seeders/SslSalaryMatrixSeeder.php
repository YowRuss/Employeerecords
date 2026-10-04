<?php

namespace Database\Seeders;

use App\Models\SalaryGrade;
use Illuminate\Database\Seeder;

class SslSalaryMatrixSeeder extends Seeder
{
    /**
     * Fill the SSL salary matrix with the Third Tranche schedule effective
     * January 1, 2026 (DBM National Budget Circular No. 601, Annex A).
     *
     * The grades below are the ones already used by this school. Step 1 of
     * each grade is already stored at these same rates, so this seeder adds
     * Steps 2 through 8 without lowering an existing Step 1.
     */
    public function run(): void
    {
        $schedule = [
            1 => [14634, 14730, 14849, 14968, 15089, 15211, 15333, 15456],
            2 => [15522, 15636, 15752, 15869, 15986, 16103, 16223, 16342],
            3 => [16486, 16610, 16732, 16856, 16982, 17106, 17234, 17360],
            4 => [17506, 17636, 17767, 17898, 18031, 18163, 18298, 18433],
            7 => [20914, 21069, 21224, 21382, 21539, 21699, 21859, 22022],
            8 => [22423, 22627, 22832, 23038, 23246, 23456, 23668, 23883],
            9 => [24329, 24523, 24720, 24917, 25117, 25318, 25521, 25725],
            10 => [26917, 27131, 27347, 27565, 27786, 28007, 28230, 28456],
            11 => [31705, 31820, 32109, 32401, 32697, 32998, 33302, 33611],
            12 => [33947, 34069, 34357, 34648, 34943, 35242, 35544, 35850],
            13 => [36125, 36283, 36599, 36919, 37244, 37572, 37904, 38241],
            14 => [38764, 39141, 39523, 39910, 40300, 40696, 41097, 41503],
            15 => [42178, 42594, 43015, 43442, 43874, 44310, 44753, 45202],
            16 => [45694, 46152, 46615, 47084, 47559, 48040, 48528, 49020],
            17 => [49562, 50066, 50576, 51092, 51614, 52144, 52678, 53221],
            18 => [53818, 54371, 54933, 55499, 56075, 56657, 57246, 57842],
            19 => [59153, 59966, 60793, 61632, 62486, 63353, 64236, 65132],
            20 => [66052, 66970, 67904, 68853, 69818, 70772, 71727, 72671],
            21 => [73303, 74337, 75388, 76456, 77542, 78645, 79692, 80831],
            22 => [81796, 82963, 84151, 85356, 86582, 87746, 89011, 90295],
        ];

        foreach ($schedule as $grade => $amounts) {
            foreach ($amounts as $index => $amount) {
                SalaryGrade::updateOrCreate(
                    ['grade' => $grade, 'step' => $index + 1],
                    ['amount' => $amount]
                );
            }
        }
    }
}
