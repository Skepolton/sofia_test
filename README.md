#SOFIA_TEST

Установка:
1. Установить модуль штатным способом через - Установленные решения
2. Проверить настройки модуля
3. Вывести комонент sofia.test.news.list в публичном разделе

Сущность - Были созданы поля перечисленные в ТЗ, хочу обратить внимание что по логике сущность Автор можно было бы вынести в отдельную таблицу, и прописать связь сразу в методе getMap класса NewsTable, я не стал тратить на это время но хотелось бы обратить на это внимание.

CRUD - Реализуются штатные через DataManager

Фильтр и поиск реализованы в админ части, в модуле SOFIA - пункт меню будет отображен отдельно в административном разделе. Так же в ТЗ была просьба реализовать фильтрацию по дате через настройки компонента в публичном разделе. (я не стал добавлять календарик для выбора даты в окне настроек компонента, даты указываются руками, по умолчанию выводятся новости за последний месяц)

Кеширование -  я использовал ttl в orm, такой кеш сбрасывается автоматом при операциях с сущностью, и так же продемонстрировал работу кеширования самого компонента вместе с шаблоном (управляется через выставление времени кеширования в параметрах компонента, так же если в настройках модуля включить удаление кеша при операциях с сущностью, будет удаляться кеш компонента)

Агенты - Реализован агент, который проверяет новости без автора и отправляет письмо администратору сайта, тут можно было бы еще добавить почтовое событие с шаблоном но я не стал тратить на это время.

События - Реализовал обработку события для добавления пункта меню в административном разделе, так же через штатные события DataManager реализовал отправку письма администратору сайта о добавлении или изменении нового элемента, тут тоже можно было использовать почтовые шаблоны но я не стал заострять на этом внимание.

Безопасность - Добавлена проверка прав и сессии пользователя


