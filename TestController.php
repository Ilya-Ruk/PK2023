<?php

/**
 * php -f TestController.php > output.txt
 */

class TestController
{
    const COMPETITIVE_GROUPS = 150; // Количество КГ

    const CG_NUMBER_LIST = [1, 2, 3, 5, 10, 15, 20, 30]; // Возможное количество мест в КГ

    const MAX_STUDENTS = 15000; // Количество абитуриентов

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
     *     result_sum,      // Сумма баллов за ВИ (без баллов за ИД)
     *     result_ia,       // Балл за ИД
     *     result_total,    // Общая сумма баллов (включая балл за ИД)
     *     priority,        // Приоритет заявления (1 - максимальный)
     *     status,          // Зачислен (1)/Не зачислен (0)
     *     position         // Позиция (начиная с 1)
     *   ]
     * ]
     */
    public static $competitiveGroupList = [];

    /**
     * studentList[studentId] => [ // Список абитуриентов с выбранными КГ и расставленными приоритетами (индекс - ID абитуриента)
     *   status,            // Зачислен (1)/Не зачислен (0)
     *   cgId,              // ID КГ, в которую зачислен абитуриент, или null (если абитуриент не поступил)
     *   priority,          // Приоритет заявления (1 - максимальный), с которым зачислен абитуриент, или null (если абитуриент не поступил)
     *
     *   applicationList[cgId] => [ // Список выбранных КГ с расставленными приоритетами (индекс - ID КГ)
     *     result_1,        // Балл за ВИ 1
     *     result_2,        // Балл за ВИ 2
     *     result_3,        // Балл за ВИ 3
     *     result_sum,      // Сумма баллов за ВИ
     *     result_ia,       // Балл за ИД
     *     result_total,    // Общая сумма баллов (включая балл за ИД)
     *     priority         // Приоритет заявления (1 - максимальный)
     *   ]
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

        echo 'Generate CG...';

        $startTime = microtime(true);

        $numberTotal = 0; // Всего мест

        for ($cgId = 0; $cgId < self::COMPETITIVE_GROUPS; $cgId++) {
            $number = self::CG_NUMBER_LIST[mt_rand(0, count(self::CG_NUMBER_LIST) - 1)];

            $numberTotal += $number;

            self::$competitiveGroupList[$cgId]['count'] = 0; // Количество зачисленных
            self::$competitiveGroupList[$cgId]['number'] = $number; // Общее количество мест
        }

        $stopTime = microtime(true);

        echo 'DONE (' . ($stopTime - $startTime) . ')' . PHP_EOL;

//        $numberTotal = 2;
//
//        self::$competitiveGroupList[0]['count'] = 0;
//        self::$competitiveGroupList[0]['number'] = 1;
//
//        self::$competitiveGroupList[1]['count'] = 0;
//        self::$competitiveGroupList[1]['number'] = 1;

//        print_r(self::$competitiveGroupList);

        /**
         * Генерируем абитуриентов с результатами ВИ, ИД и выбранными КГ с расставленными приоритетами
         */

        echo 'Generate students...';

        $startTime = microtime(true);

        $appTotal = 0; // Всего заявлений

        for ($studentId = 0; $studentId < self::MAX_STUDENTS; $studentId++) {
            self::$studentList[$studentId]['status'] = self::STATUS_NO; // Не зачислен
            self::$studentList[$studentId]['cgId'] = null;
            self::$studentList[$studentId]['priority'] = null;

            $appCount = mt_rand(1, self::MAX_APPLICATIONS); // Количество заявлений для текущего абитуриента

            $sigma = 0.5;           // [-0.5..0.5]
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

                $result_1 = mt_rand(self::MIN_RESULT, self::MAX_RESULT);
                $result_2 = mt_rand(self::MIN_RESULT, self::MAX_RESULT);
                $result_3 = mt_rand(self::MIN_RESULT, self::MAX_RESULT);

                self::$studentList[$studentId]['applicationList'][$cgId]['result_1'] = $result_1;
                self::$studentList[$studentId]['applicationList'][$cgId]['result_2'] = $result_2;
                self::$studentList[$studentId]['applicationList'][$cgId]['result_3'] = $result_3;

                $resultSum = $result_1 + $result_2 + $result_3;

                self::$studentList[$studentId]['applicationList'][$cgId]['result_sum'] = $resultSum;

                $resultIA = mt_rand(self::MIN_IA, self::MAX_IA);

                self::$studentList[$studentId]['applicationList'][$cgId]['result_ia'] = $resultIA;

                self::$studentList[$studentId]['applicationList'][$cgId]['result_total'] = $resultSum + $resultIA;

                self::$studentList[$studentId]['applicationList'][$cgId]['priority'] = $priority;

                $appTotal++;
            }
        }

        $stopTime = microtime(true);

        echo 'DONE (' . ($stopTime - $startTime) . ')' . PHP_EOL;

//        $appTotal = 6;
//
//        self::$studentList[0]['status'] = self::STATUS_NO;
//        self::$studentList[0]['cgId'] = null;
//        self::$studentList[0]['applicationList'] = [
//            0 => ['result_total' => 290, 'priority' => 1],
//            1 => ['result_total' => 280, 'priority' => 2]
//        ];
//
//        self::$studentList[1]['status'] = self::STATUS_NO;
//        self::$studentList[1]['cgId'] = null;
//        self::$studentList[1]['applicationList'] = [
//            0 => ['result_total' => 280, 'priority' => 1],
//            1 => ['result_total' => 270, 'priority' => 2]
//        ];
//
//        self::$studentList[2]['status'] = self::STATUS_NO;
//        self::$studentList[2]['cgId'] = null;
//        self::$studentList[2]['applicationList'] = [
//            0 => ['result_total' => 270, 'priority' => 2],
//            1 => ['result_total' => 260, 'priority' => 1]
//        ];

//        print_r(self::$studentList);

        /**
         * Заполняем КГ по заявлениям абитуриентов
         */

        echo 'Filling CG...';

        $startTime = microtime(true);

        for ($studentId = 0; $studentId < self::MAX_STUDENTS; $studentId++) {
            if (!isset(self::$studentList[$studentId]['applicationList'])) { // У абитуриента нет заявлений
                continue;
            }

            foreach (self::$studentList[$studentId]['applicationList'] as $cgId => $cgData) {
                self::$competitiveGroupList[$cgId]['applicationList'][$studentId]['result_1'] = $cgData['result_1'];
                self::$competitiveGroupList[$cgId]['applicationList'][$studentId]['result_2'] = $cgData['result_2'];
                self::$competitiveGroupList[$cgId]['applicationList'][$studentId]['result_3'] = $cgData['result_3'];
                self::$competitiveGroupList[$cgId]['applicationList'][$studentId]['result_sum'] = $cgData['result_sum'];
                self::$competitiveGroupList[$cgId]['applicationList'][$studentId]['result_ia'] = $cgData['result_ia'];
                self::$competitiveGroupList[$cgId]['applicationList'][$studentId]['result_total'] = $cgData['result_total'];
                self::$competitiveGroupList[$cgId]['applicationList'][$studentId]['priority'] = $cgData['priority'];
                self::$competitiveGroupList[$cgId]['applicationList'][$studentId]['status'] = self::STATUS_NO; // Не зачислен
                self::$competitiveGroupList[$cgId]['applicationList'][$studentId]['position'] = null;
            }
        }

        $stopTime = microtime(true);

        echo 'DONE (' . ($stopTime - $startTime) . ')' . PHP_EOL;

//        print_r(self::$competitiveGroupList);

        /**
         * Сортируем абитуриентов в КГ по убыванию:
         * - общая сумма баллов (включая балл за ИД);
         * - сумма баллов за ВИ (без баллов за ИД);
         * - балл за ВИ 1;
         * - балл за ВИ 2;
         * - балл за ВИ 3
         */

        echo 'Sorting CG...';

        $startTime = microtime(true);

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

        $stopTime = microtime(true);

        echo 'DONE (' . ($stopTime - $startTime) . ')' . PHP_EOL;

        /**
         * Заполняем позицию в КГ
         */

        echo 'Set position...';

        $startTime = microtime(true);

        for ($cgId = 0; $cgId < self::COMPETITIVE_GROUPS; $cgId++) {
            if (!isset(self::$competitiveGroupList[$cgId]['applicationList'])) { // В КГ нет заявлений
                continue;
            }

            $position = 1;

            foreach (self::$competitiveGroupList[$cgId]['applicationList'] as $studentId => &$studentData) {
                $studentData['position'] = $position;

                $position++;
            }
        }

        $stopTime = microtime(true);

        echo 'DONE (' . ($stopTime - $startTime) . ')' . PHP_EOL;

//        print_r(self::$competitiveGroupList);

        /**
         * Проводим зачисление
         */

        echo 'Algorithm...';

        $startTime = microtime(true);

        $studentTotal = 0; // Всего зачисленных

        while (true) {
            $stop = true;

            for ($cgId = 0; $cgId < self::COMPETITIVE_GROUPS; $cgId++) {
                if (!isset(self::$competitiveGroupList[$cgId]['applicationList'])) { // В КГ нет заявлений
                    continue;
                }

                $count = 0; // Количество зачисленных в КГ
                $number = self::$competitiveGroupList[$cgId]['number']; // Общее количество мест в КГ

                foreach (self::$competitiveGroupList[$cgId]['applicationList'] as $studentId => &$studentData) {
                    if (self::$studentList[$studentId]['status'] == self::STATUS_NO) { // Абитуриент не зачислен
                        // Помечаем абитуриента зачисленным

//                        echo '.';

                        self::$studentList[$studentId]['status'] = self::STATUS_YES; // Помечаем абитуриента зачисленным (в заявлении)
                        self::$studentList[$studentId]['cgId'] = $cgId; // Сохраняем ID КГ, в которую зачислен абитуриент (в заявлении)
                        self::$studentList[$studentId]['priority'] = $studentData['priority']; // Сохраняем приоритет заявления, с которым зачислен абитуриент (в заявлении)

                        $studentData['status'] = self::STATUS_YES; // Помечаем абитуриента зачисленным (в текущей КГ)

                        $studentTotal++;

                        $stop = false;
                    } elseif (self::$studentList[$studentId]['cgId'] != $cgId) { // Абитуриент зачислен в другую КГ
                        if ($studentData['priority'] < self::$studentList[$studentId]['priority']) { // Абитуриент зачислен в другую КГ с меньшим приоритетом
                            // Снимаем отметку о зачислении абитуриента из КГ с меньшим приоритетом и помечаем абитуриента зачисленным в текущую КГ с большим приоритетом

//                            echo '!';

                            self::$competitiveGroupList[self::$studentList[$studentId]['cgId']]['applicationList'][$studentId]['status'] = self::STATUS_NO; // Снимаем отметку о зачислении абитуриента из КГ с меньшим приоритетом

                            self::$studentList[$studentId]['cgId'] = $cgId; // Обновляем ID КГ, в которую зачислен абитуриент (в заявлении)
                            self::$studentList[$studentId]['priority'] = $studentData['priority']; // Обновляем приоритет заявления, с которым зачислен абитуриент (в заявлении)

                            $studentData['status'] = self::STATUS_YES; // Помечаем абитуриента зачисленным (в текущей КГ)

                            $stop = false;
                        }
                    }

                    if ($studentData['status'] == self::STATUS_YES) {
                        $count++;
                    }

                    if ($count >= $number) {
                        break;
                    }
                }

                self::$competitiveGroupList[$cgId]['count'] = $count; // Сохраняем количество зачисленных в КГ
            }

//            echo PHP_EOL;

            if ($stop === true) {
                break;
            }
        }

        $stopTime = microtime(true);

        echo 'DONE (' . ($stopTime - $startTime) . ')' . PHP_EOL;

//        print_r(self::$competitiveGroupList);

        /**
         * Проверяем зачисление
         */

        echo 'Test...';

        $startTime = microtime(true);

        for ($cgId = 0; $cgId < self::COMPETITIVE_GROUPS; $cgId++) {
            if (!isset(self::$competitiveGroupList[$cgId]['applicationList'])) { // В КГ нет заявлений
                continue;
            }

            $count = 0; // Количество зачисленных в КГ

            foreach (self::$competitiveGroupList[$cgId]['applicationList'] as $studentId => &$studentData) {
                if ($studentData['status'] == self::STATUS_YES) {
                    $count++;
                }

                $currentPosition = $studentData['position'];
                $currentPriority = $studentData['priority'];

                $bestPosition = PHP_INT_MAX;
                $bestPriority = null;
                $bestCgId = null;

                foreach (self::$studentList[$studentId]['applicationList'] as $cgId2 => &$cgData2) {
                    $position = self::$competitiveGroupList[$cgId2]['applicationList'][$studentId]['position'];
                    $priority = self::$competitiveGroupList[$cgId2]['applicationList'][$studentId]['priority'];

                    if ($position < $bestPosition) {
                        $bestPosition = $position;
                        $bestPriority = $priority;
                        $bestCgId = $cgId2;
                    }
                }

                $count2 = 0; // Количество зачисленных в КГ (2)

                foreach (self::$competitiveGroupList[$bestCgId]['applicationList'] as $studentId2 => &$studentData2) {
                    if ($studentData2['status'] == self::STATUS_YES) {
                        $count2++;
                    }
                }

                /**
                 * Проверка на возможность зачисления
                 */
                if (self::$studentList[$studentId]['status'] == self::STATUS_NO && $bestPosition <= $count2) {
                    echo 'ERROR 1: cgId = ' . $cgId . ' studentId = ' . $studentId . ' bestPosition = ' . $bestPosition . ' count2 = ' . $count2 . PHP_EOL;
                }

                /**
                 * Проверка на возможность зачисления с лучшими условиями (лучше позиция и приоритет)
                 */
                if ($studentData['status'] == self::STATUS_YES && $bestPosition < $currentPosition && $bestPriority < $currentPriority && $bestPosition <= $count2) {
                    echo 'ERROR 2: cgId = ' . $cgId . ' studentId = ' . $studentId . ' bestPosition = ' . $bestPosition . ' currentPosition = ' . $currentPosition . ' bestPriority = ' . $bestPriority . ' priority = ' . $currentPriority . ' count2 = ' . $count2 . PHP_EOL;
                }
            }

            /**
             * Проверка на превышение общего количества мест в КГ
             */
            if ($count > self::$competitiveGroupList[$cgId]['number']) {
                echo 'ERROR 3: cgId = ' . $cgId . ' count = ' . $count . ' number = ' . self::$competitiveGroupList[$cgId]['number'] . PHP_EOL;
            }
        }

        $stopTime = microtime(true);

        echo 'DONE (' . ($stopTime - $startTime) . ')' . PHP_EOL;

        /**
         * Выводим:
         * - общее количество мест;
         * - общее количество заявлений;
         * - общее количество зачисленных
         */

        echo PHP_EOL;

        echo 'Number total: ' . $numberTotal . PHP_EOL; // Всего мест
        echo 'Application total: ' . $appTotal . PHP_EOL; // Всего заявлений
        echo 'Student total: ' . $studentTotal . PHP_EOL; // Всего зачисленных

        /**
         * Выводим распределение заявлений по КГ (для контроля)
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
         * Выводим рейтинг:
         * - позиция;
         * - ID абитуриента;
         * - общая сумма баллов (включая балл за ИД);
         * - балл за ИД;
         * - сумма баллов за ВИ (без баллов за ИД);
         * - балл за ВИ 1;
         * - балл за ВИ 2;
         * - балл за ВИ 3;
         * - приоритет заявления;
         * - признак зачисления (* - зачислен)
         */

        for ($cgId = 0; $cgId < self::COMPETITIVE_GROUPS; $cgId++) {
            if (!isset(self::$competitiveGroupList[$cgId]['applicationList'])) { // В КГ нет заявлений
                continue;
            }

            printf("\n----- %d (%d / %d) -----\n\n", $cgId, self::$competitiveGroupList[$cgId]['count'], self::$competitiveGroupList[$cgId]['number']);

            foreach (self::$competitiveGroupList[$cgId]['applicationList'] as $studentId => &$studentData) {
                printf("%3d %5d %3d %3d %3d %3d %3d %3d %2d %s\n", $studentData['position'], $studentId, $studentData['result_total'], $studentData['result_ia'], $studentData['result_sum'], $studentData['result_1'], $studentData['result_2'], $studentData['result_3'], $studentData['priority'], ($studentData['status'] == self::STATUS_YES ? '*' : ''));
            }
        }
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
