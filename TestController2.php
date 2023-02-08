<?php

/**
 * php -f TestController2.php > output2.txt
 */

class TestController2
{
    const STATUS_NO = 0; // Не зачислен
    const STATUS_YES = 1; // Зачислен

    /**
     * competitiveGroupList[cgId] => [ // Список КГ с заявлениями (индекс - ID КГ)
     *   count,             // Количество зачисленных
     *   number,            // Общее количество мест
     *
     *   applicationList[studentId] => [ // Список заявлений (индекс - ID абитуриента)
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
     *     result_total,    // Общая сумма баллов (включая балл за ИД)
     *     priority         // Приоритет заявления (1 - максимальный)
     *   ]
     * ]
     */
    public static $studentList = [];

    public function actionRun()
    {
        /**
         * Генерируем КГ с общим количеством мест
         */

        self::$competitiveGroupList[0]['count'] = 0;
        self::$competitiveGroupList[0]['number'] = 1;
        self::$competitiveGroupList[0]['applicationList'] = [];

        self::$competitiveGroupList[1]['count'] = 0;
        self::$competitiveGroupList[1]['number'] = 1;
        self::$competitiveGroupList[1]['applicationList'] = [];

        $numberTotal = 0; // Всего мест

        for ($cgId = 0; $cgId < count(self::$competitiveGroupList); $cgId++) {
            $numberTotal += self::$competitiveGroupList[$cgId]['number'];
        }

//        print_r(self::$competitiveGroupList);

        /**
         * Генерируем абитуриентов с результатами ВИ, ИД и выбранными КГ с расставленными приоритетами
         */

        self::$studentList[0]['status'] = self::STATUS_NO;
        self::$studentList[0]['cgId'] = null;
        self::$studentList[0]['priority'] = null;
        self::$studentList[0]['applicationList'] = [
            0 => ['result_total' => 260, 'priority' => 2],
            1 => ['result_total' => 240, 'priority' => 1]
        ];

        self::$studentList[1]['status'] = self::STATUS_NO;
        self::$studentList[1]['cgId'] = null;
        self::$studentList[1]['priority'] = null;
        self::$studentList[1]['applicationList'] = [
            0 => ['result_total' => 250, 'priority' => 1],
            1 => ['result_total' => 275, 'priority' => 2]
        ];

        $appTotal = 0; // Всего заявлений

        for ($studentId = 0; $studentId < count(self::$studentList); $studentId++) {
            $appTotal += count(self::$studentList[$studentId]['applicationList']);
        }

//        print_r(self::$studentList);

        /**
         * Заполняем КГ по заявлениям абитуриентов
         */

        for ($studentId = 0; $studentId < count(self::$studentList); $studentId++) {
            if (count(self::$studentList[$studentId]['applicationList']) == 0) { // У абитуриента нет заявлений
                continue;
            }

            foreach (self::$studentList[$studentId]['applicationList'] as $cgId => $cgData) {
                self::$competitiveGroupList[$cgId]['applicationList'][$studentId]['result_total'] = $cgData['result_total'];
                self::$competitiveGroupList[$cgId]['applicationList'][$studentId]['priority'] = $cgData['priority'];
                self::$competitiveGroupList[$cgId]['applicationList'][$studentId]['status'] = self::STATUS_NO; // Не зачислен
                self::$competitiveGroupList[$cgId]['applicationList'][$studentId]['position'] = null;
            }
        }

//        print_r(self::$competitiveGroupList);

        /**
         * Сортируем абитуриентов в КГ по убыванию общей суммы баллов (включая балл за ИД)
         */

        for ($cgId = 0; $cgId < count(self::$competitiveGroupList); $cgId++) {
            if (count(self::$competitiveGroupList[$cgId]['applicationList']) == 0) { // В КГ нет заявлений
                continue;
            }

            uasort(self::$competitiveGroupList[$cgId]['applicationList'], function ($a, $b) {
                if ($a['result_total'] < $b['result_total']) {
                    return 1;
                }
                elseif ($a['result_total'] > $b['result_total']) {
                    return -1;
                }

                return 0;
            });
        }

//        print_r(self::$competitiveGroupList);

        /**
         * Заполняем позицию в КГ
         */

        for ($cgId = 0; $cgId < count(self::$competitiveGroupList); $cgId++) {
            if (count(self::$competitiveGroupList[$cgId]['applicationList']) == 0) { // В КГ нет заявлений
                continue;
            }

            $position = 1;

            foreach (self::$competitiveGroupList[$cgId]['applicationList'] as $studentId => &$studentData) {
                $studentData['position'] = $position;

                $position++;
            }
        }

//        print_r(self::$competitiveGroupList);

        /**
         * Проводим зачисление
         */

        $studentTotal = 0; // Всего зачисленных

        while (true) {
            $stop = true;

//            echo PHP_EOL;

            for ($cgId = 0; $cgId < count(self::$competitiveGroupList); $cgId++) {
                if (count(self::$competitiveGroupList[$cgId]['applicationList']) == 0) { // В КГ нет заявлений
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

            if ($stop === true) {
                break;
            }
        }

//        print_r(self::$competitiveGroupList);

        /**
         * Проверяем зачисление
         */

        for ($cgId = 0; $cgId < count(self::$competitiveGroupList); $cgId++) {
            if (count(self::$competitiveGroupList[$cgId]['applicationList']) == 0) { // В КГ нет заявлений
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
         * Выводим рейтинг:
         * - позиция;
         * - ID абитуриента;
         * - общая сумма баллов (включая балл за ИД);
         * - приоритет заявления;
         * - признак зачисления (* - зачислен)
         */

        for ($cgId = 0; $cgId < count(self::$competitiveGroupList); $cgId++) {
            if (count(self::$competitiveGroupList[$cgId]['applicationList']) == 0) { // В КГ нет заявлений
                continue;
            }

            printf("\n----- %d (%d / %d) -----\n\n", $cgId, self::$competitiveGroupList[$cgId]['count'], self::$competitiveGroupList[$cgId]['number']);

            foreach (self::$competitiveGroupList[$cgId]['applicationList'] as $studentId => &$studentData) {
                printf("%3d %5d %3d %2d %s\n", $studentData['position'], $studentId, $studentData['result_total'], $studentData['priority'], ($studentData['status'] == self::STATUS_YES ? '*' : ''));
            }
        }
    }
}

(new TestController2())->actionRun();
