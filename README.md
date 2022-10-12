# Утилиты для приемной кампании 2023

## TestController

1. Формирование КГ со случайным количеством мест
2. Формирование студентов со случайными баллами по трем вступительным испытаниям (ВИ), случайным баллом за индивидуальное достижение (ИД) и случайным выбором (по умолчанию используется нормальное распределение) случайного количества КГ с расстановкой приоритетов зачисления (1 - максимальный)
3. Заполнение КГ по заявлениям абитуриентов
4. Удаление КГ в которых нет заявлений или количество поданных заявлений меньше числа мест в КГ (можно закомментировать этот блок)
5. Сортировка (ранжирование) списков абитуриентов по убыванию общей суммы баллов (включая балл за ИД), суммы баллов (только за ВИ), баллов за 1-3 ВИ
6. Зачисление абитуриентов с учетом приоритетов
7. Вывод статистических данных (общее количество мест, количество поданных заявлений и количество зачисленных абитуриентов) и полных списков абитуриентов и КГ (более 20 МБайт)

### Требования

PHP >= 7.0

### Запуск

> php -f TestController.php > output.txt