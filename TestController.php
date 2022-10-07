<?php

/**
 * php -f TestController.php > output.txt
 */

class TestController
{
    const COMPETITIVE_GROUPS = 150; // Количество КГ

    const CG_NUMBER_LIST = [1, 2, 3, 5, 10, 15, 20, 30]; // Возможное количество мест в КГ

    const MAX_STUDENTS = 13500; // Количество абитуриентов

    const MIN_RESULT = 50; // Минимальный балл за ВИ
    const MAX_RESULT = 100; // Максимальный балл за ВИ

    const MIN_IA = 0; // Минимальный балл за ИД
    const MAX_IA = 10; // Максимальный балл за ИД

    const MAX_APPLICATIONS = 5; // Максимальное количество заявлений от одного абитуриента

    const STATUS_NO = 0; // Не зачислен
    const STATUS_YES = 1; // Зачислен

    /**
     * competitiveGroupList[cgId] => [ // Список КГ с заявлениями (индекс - ID КГ)
     *   count,             // Количество зачисленных
     *   number,            // Общее количество мест
     *
     *   applicationList[studentId] => [ // Список заявлений (индекс - ID абитуриента)
     *     result_1,        // Балл за ВИ 1
     *     result_2,        // Балл за ВИ 2
     *     result_3,        // Балл за ВИ 3
     *     result_sum,      // Сумма баллов за ВИ
     *     result_ia,       // Балл за ИД
     *     result_total,    // Общая сумма баллов, включая баллы за ИД
     *     status,          // Зачислен/Не зачислен
     *     priority         // Приоритет заявления для абитуриента
     *   ]
     * ]
     */
    public static $competitiveGroupList = [];

    /**
     * studentList[studentId] => [ // Список абитуриентов с выбранными КГ и расставленными приоритетами (индекс - ID абитуриента)
     *   result_1,        // Балл за ВИ 1
     *   result_2,        // Балл за ВИ 2
     *   result_3,        // Балл за ВИ 3
     *   result_sum,      // Сумма баллов за ВИ
     *   result_ia,       // Балл за ИД
     *   result_total,    // Общая сумма баллов, включая баллы за ИД
     *   status,          // Зачислен/Не зачислен
     *
     *   applicationList[cgId] => priority // Список выбранных КГ с расставленными приоритетами (индекс - ID КГ)
     * ]
     */
    public static $studentList = [];

    /**
     * Переменные для получения нормального распределения
     */
    private static $X1, $X2;
    private static $call = 0;

    public function actionRun()
    {
//        mt_srand(0); // Можно разкомментировать для воспроизводимости расчета

//        self::plotDistribution(); // Вывод гистограммы распределения на экран (только для проверки случайной функции)
//        die;

        /**
         * Генерируем КГ с общим количеством мест
         */

        $numberTotal = 0; // Всего мест

        for ($cgId = 0; $cgId < self::COMPETITIVE_GROUPS; $cgId++) {
            $number = self::CG_NUMBER_LIST[mt_rand(0, count(self::CG_NUMBER_LIST) - 1)];

            $numberTotal += $number;

            self::$competitiveGroupList[$cgId]['count'] = 0; // Количество зачисленных
            self::$competitiveGroupList[$cgId]['number'] = $number; // Общее количество мест
        }

        /**
         * Генерируем студентов с результатами ВИ, ИД и выбранными КГ с расставленными приоритетами
         */

        $appTotal = 0; // Всего заявлений

        for ($studentId = 0; $studentId < self::MAX_STUDENTS; $studentId++) {
            $result_1 = mt_rand(self::MIN_RESULT, self::MAX_RESULT);
            $result_2 = mt_rand(self::MIN_RESULT, self::MAX_RESULT);
            $result_3 = mt_rand(self::MIN_RESULT, self::MAX_RESULT);

            self::$studentList[$studentId]['result_1'] = $result_1;
            self::$studentList[$studentId]['result_2'] = $result_2;
            self::$studentList[$studentId]['result_3'] = $result_3;

            $resultSum = $result_1 + $result_2 + $result_3;

            self::$studentList[$studentId]['result_sum'] = $resultSum;

            $resultIA = mt_rand(self::MIN_IA, self::MAX_IA);

            self::$studentList[$studentId]['result_ia'] = $resultIA;

            self::$studentList[$studentId]['result_total'] = $resultSum + $resultIA;

            self::$studentList[$studentId]['status'] = self::STATUS_NO; // Не зачислен

            $appCount = mt_rand(1, self::MAX_APPLICATIONS); // Количество заявлений для текущего абитуриента

            $sigma = 0.5;           // [-0.5..0.5]
//            $sigma_2 = 2 * $sigma;  // [-1.0..1.0]
            $sigma_3 = 3 * $sigma;  // [-1.5..1.5]

            for ($priority = 1; $priority <= $appCount; $priority++) {
                while (true) {
                    /**
                     * Равномерное распределение
                     */

//                    $cgId = mt_rand(0, self::COMPETITIVE_GROUPS - 1);

                    /**
                     * Нормальное распределение
                     */

                    while (true) {
                        $rand = self::RandomGaussian(0.0, $sigma);
                        $cgId = ($rand + $sigma_3) * (self::COMPETITIVE_GROUPS - 1) / (2 * $sigma_3);

                        if ($cgId >= 0 && $cgId < self::COMPETITIVE_GROUPS) {
                            break;
                        }
                    }

                    if (!isset(self::$studentList[$studentId]['applicationList'][$cgId])) {
                        break;
                    }
                }

                self::$studentList[$studentId]['applicationList'][$cgId] = $priority;

                $appTotal++;
            }
        }

        /**
         * Заполняем КГ по заявлениям абитуриентов
         */

        for ($studentId = 0; $studentId < self::MAX_STUDENTS; $studentId++) {
            if (!isset(self::$studentList[$studentId]['applicationList'])) { // У абитуриента нет заявлений
                continue;
            }

            $result_1 = self::$studentList[$studentId]['result_1'];
            $result_2 = self::$studentList[$studentId]['result_2'];
            $result_3 = self::$studentList[$studentId]['result_3'];
            $result_sum = self::$studentList[$studentId]['result_sum'];
            $result_ia = self::$studentList[$studentId]['result_ia'];
            $result_total = self::$studentList[$studentId]['result_total'];

            $status = self::$studentList[$studentId]['status'];

            foreach (self::$studentList[$studentId]['applicationList'] as $cgId => $priority) {
                self::$competitiveGroupList[$cgId]['applicationList'][$studentId]['result_1'] = $result_1;
                self::$competitiveGroupList[$cgId]['applicationList'][$studentId]['result_2'] = $result_2;
                self::$competitiveGroupList[$cgId]['applicationList'][$studentId]['result_3'] = $result_3;
                self::$competitiveGroupList[$cgId]['applicationList'][$studentId]['result_sum'] = $result_sum;
                self::$competitiveGroupList[$cgId]['applicationList'][$studentId]['result_ia'] = $result_ia;
                self::$competitiveGroupList[$cgId]['applicationList'][$studentId]['result_total'] = $result_total;

                self::$competitiveGroupList[$cgId]['applicationList'][$studentId]['status'] = $status;
                self::$competitiveGroupList[$cgId]['applicationList'][$studentId]['priority'] = $priority;
            }
        }

        /**
         * Убираем КГ в которых нет заявлений или количество поданных заявлений меньше числа мест в КГ
         */

        for ($cgId = 0; $cgId < self::COMPETITIVE_GROUPS; $cgId++) {
            if (!isset(self::$competitiveGroupList[$cgId]['applicationList'])) { // В КГ нет заявлений
                $numberTotal -= self::$competitiveGroupList[$cgId]['number'];

                unset(self::$competitiveGroupList[$cgId]);

                continue;
            }

            if (count(self::$competitiveGroupList[$cgId]['applicationList']) < self::$competitiveGroupList[$cgId]['number']) { // Количество поданных заявлений меньше числа мест в КГ
                $appTotal -= count(self::$competitiveGroupList[$cgId]['applicationList']);
                $numberTotal -= self::$competitiveGroupList[$cgId]['number'];

                unset(self::$competitiveGroupList[$cgId]);
            }
        }

        /**
         * Сортируем абитуриентов в КГ по убыванию:
         * - Общая сумма баллов, включая баллы за ИД
         * - Сумма баллов за ВИ, без баллов за ИД
         * - Балл за ВИ 1
         * - Балл за ВИ 2
         * - Балл за ВИ 3
         */

        for ($cgId = 0; $cgId < self::COMPETITIVE_GROUPS; $cgId++) {
            if (!isset(self::$competitiveGroupList[$cgId]['applicationList'])) { // В КГ нет заявлений
                continue;
            }

            uasort(self::$competitiveGroupList[$cgId]['applicationList'], function ($a, $b) {
                if ($a['result_total'] < $b['result_total']) {
                    return 1;
                }

                if ($a['result_total'] > $b['result_total']) {
                    return -1;
                }

                if ($a['result_sum'] < $b['result_sum']) {
                    return 1;
                }

                if ($a['result_sum'] > $b['result_sum']) {
                    return -1;
                }

                if ($a['result_1'] < $b['result_1']) {
                    return 1;
                }

                if ($a['result_1'] > $b['result_1']) {
                    return -1;
                }

                if ($a['result_2'] < $b['result_2']) {
                    return 1;
                }

                if ($a['result_2'] > $b['result_2']) {
                    return -1;
                }

                if ($a['result_3'] < $b['result_3']) {
                    return 1;
                }

                if ($a['result_3'] > $b['result_3']) {
                    return -1;
                }

                return 0;
            });
        }

        /**
         * Проводим зачисление
         */

        $studentTotal = 0; // Всего зачисленных

        for ($priority = 1; $priority <= self::MAX_APPLICATIONS; $priority++) {
//            echo $priority . PHP_EOL;

            for ($cgId = 0; $cgId < self::COMPETITIVE_GROUPS; $cgId++) {
                if (!isset(self::$competitiveGroupList[$cgId]['applicationList'])) { // В КГ нет заявлений
                    continue;
                }

                $count = self::$competitiveGroupList[$cgId]['count']; // Количество уже зачисленных в КГ
                $number = self::$competitiveGroupList[$cgId]['number']; // Общее количество мест в КГ

                foreach (self::$competitiveGroupList[$cgId]['applicationList'] as $studentId => &$studentData) {
                    if ($count >= $number) { // В КГ больше нет вакантных мест
                        break;
                    }

                    if (self::$studentList[$studentId]['status'] == self::STATUS_YES) { // Абитуриент уже зачислен (по более высокому приоритету)
                        continue;
                    }

                    if ($studentData['priority'] != $priority) { // Приоритет в заявлении не соответствует итерации по приоритетам
                        continue;
                    }

//                    echo '.';

                    self::$studentList[$studentId]['status'] = self::STATUS_YES; // Помечаем абитуриента зачисленным (в заявлении)

                    $studentData['status'] = self::STATUS_YES; // Помечаем абитуриента зачисленным (в КГ)

                    $count++;
                    $studentTotal++;
                }

                self::$competitiveGroupList[$cgId]['count'] = $count; // Сохраняем количество зачисленных в КГ
            }

//            echo PHP_EOL;
        }

        /**
         * Статистические данные
         */

        echo 'Number total: ' . $numberTotal . PHP_EOL; // Всего мест
        echo 'Application total: ' . $appTotal . PHP_EOL; // Всего заявлений
        echo 'Student total: ' . $studentTotal . PHP_EOL; // Всего зачисленных

        /**
         * Распределение заявлений по КГ (для контроля распределения)
         */

//        $maxCount = 0;
//
//        for ($cgId = 0; $cgId < self::COMPETITIVE_GROUPS; $cgId++) {
//            if (!isset(self::$competitiveGroupList[$cgId]['applicationList'])) { // В КГ нет заявлений
//                continue;
//            }
//
//            if (count(self::$competitiveGroupList[$cgId]['applicationList']) > $maxCount) {
//                $maxCount = count(self::$competitiveGroupList[$cgId]['applicationList']);
//            }
//        }
//
//        $maxWidth = 80;
//
//        for ($cgId = 0; $cgId < self::COMPETITIVE_GROUPS; $cgId++) {
//            if (!isset(self::$competitiveGroupList[$cgId]['applicationList'])) { // В КГ нет заявлений
//                continue;
//            }
//
//            printf("[%2d] ", $cgId);
//
//            for ($i = 0; $i < count(self::$competitiveGroupList[$cgId]['applicationList']) * $maxWidth / $maxCount; $i++) {
//                printf("*");
//            }
//
//            printf("\n");
//        }

        /**
         * Выводим:
         * - Список абитуриентов с выбранными КГ и расставленными приоритетами
         * - Список КГ с заявлениями
         *
         * При заданных параметрах, более 20 Мбайт!
         */

        print_r(self::$studentList);
        print_r(self::$competitiveGroupList);
    }

    /**
     * Получение нормального распределения из равномерного
     *
     * https://phoxis.org/2013/05/04/generating-random-numbers-from-normal-distribution-in-c/
     *
     * @param float $mu
     * @param float $sigma
     *
     * @return float
     */
    private static function randomGaussian(float $mu = 0.0, float $sigma = 1.0): float
    {
        if (self::$call == 1) {
            self::$call = !self::$call;

            return ($mu + $sigma * (float)self::$X2);
        }

        do {
            $U1 = -1 + ((float)mt_rand() / mt_getrandmax()) * 2;
            $U2 = -1 + ((float)mt_rand() / mt_getrandmax()) * 2;

            $W = pow($U1, 2) + pow($U2, 2);
        } while ($W >= 1 || $W == 0);

        $mult = sqrt((-2 * log($W)) / $W);

        self::$X1 = $U1 * $mult;
        self::$X2 = $U2 * $mult;

        self::$call = !self::$call;

        return ($mu + $sigma * (float)self::$X1);
    }

    /**
     * Вывод гистограммы распределения на экран (развернута на 90 градусов)
     *
     * @return void
     */
    private static function plotDistribution()
    {
        $min = -1.5;
        $max = 1.5;

        $intervals = 20;

        $samples = 10000;

        $maxWidth = 80;

        $plot = []; // $intervals + 1
        $range = []; // $intervals + 1

        for ($i = 0; $i <= $intervals; $i++) {
            $plot[$i] = 0;
        }

        $range[0] = $min;

        for ($i = 1; $i <= $intervals; $i++) {
            $range[$i] = $range[0] + ($max - $min) / $intervals * $i;
        }

        $rangeOut = false;

        for ($i = 0; $i < $samples; $i++) {
//            $value = ((float)mt_rand() / mt_getrandmax()) - 0.5; // Равномерное распределение
            $value = self::randomGaussian(0.0, 0.5); // Нормальное распределение

            $found = false;

            for ($j = 0; $j < $intervals; $j++) {
                if ($value >= $range[$j] && $value < $range[$j + 1]) {
                    $plot[$j]++;

                    $found = true;

                    break;
                }
            }

            if ($found === false) {
                $rangeOut = true;
            }
        }

        $maxPlot = 0;

        for ($i = 0; $i <= $intervals; $i++) {
            if ($plot[$i] > $maxPlot) {
                $maxPlot = $plot[$i];
            }
        }

        $m = (float)$maxPlot / $maxWidth;

        for ($i = 0; $i <= $intervals; $i++) {
            $plot[$i] = (float)$plot[$i] / $m;
        }

        for ($i = 0; $i < $intervals; $i++) {
            printf("[%+.3f - %+.3f]: ", $range[$i], $range[$i + 1]);

            for ($j = 0; $j < $plot[$i]; $j++) {
                printf("*");
            }

            printf("\n");
        }

        if ($rangeOut === true) {
            printf("WARNING: Plotting out of range!\n");
        }
    }
}

(new TestController())->actionRun();
