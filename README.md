# Утилиты для приемной кампании 2023

## TestController

1. Формирование КГ со случайным количеством вакантных мест
2. Формирование студентов со случайным выбором (по умолчанию используется нормальное распределение) случайного количества КГ с расстановкой приоритетов зачисления (1 - максимальный), случайными баллами по трем вступительным испытаниям (ВИ) и случайным баллом за индивидуальное достижение (ИД)
3. Заполнение КГ по заявлениям абитуриентов
4. Сортировка (ранжирование) списков абитуриентов по убыванию общей суммы баллов (включая балл за ИД), суммы баллов за ВИ (без баллов за ИД), баллов за ВИ 1-3
5. Расстановка позициий заявлений в КГ
6. Зачисление абитуриентов с учетом приоритетов
7. Проверка зачисления абитуриентов
8. Вывод статистических данных (общее количество мест, общее количество заявлений и общее количество зачисленных)
9. Вывод итогового рейтинга с отметкой о зачислении

### Требования

PHP >= 7.0

### Запуск

> php -f TestController.php > output.txt
