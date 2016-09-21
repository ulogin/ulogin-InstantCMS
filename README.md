# uLogin

Donate link: http://ulogin.ru  
Tags: ulogin, login, social, authorization  
Tested up to: 2.4 
Stable tag: 2.0.7
License: GPLv2  

**uLogin** — это инструмент, который позволяет пользователям получить единый доступ к различным Интернет-сервисам без необходимости повторной регистрации,
а владельцам сайтов — получить дополнительный приток клиентов из социальных сетей и популярных порталов (Google, Яндекс, Mail.ru, ВКонтакте, Facebook и др.)


## Установка

В качестве пакета для установки используйте архив ulogin.install.zip. 
Установка пакета uLogin стандартная для пакетов дополнения InstantCMS 2.x.

- в разделе "Компоненты" панели управления вашего сайта нажмите "Установить пакет дополнения"
- следуйте инструкциям мастера установки компонентов


## Страница настроек компонента

Вы можете создать свой виджет uLogin и редактировать его самостоятельно:

для создания виджета необходимо зайти в Личный Кабинет (ЛК) на сайте http://ulogin.ru/lk.php,
добавить свой сайт к списку Мои сайты и на вкладке Виджеты добавить новый виджет. После этого вы можете отредактировать свой виджет.

**Важно!** Для успешной работы плагина необходимо включить в обязательных полях профиля поле Еmail в Личном кабинете uLogin.

На своём сайте на странице Компоненты в настройках uLogin укажите значение поля uLogin ID.


## Виджеты компонента 

Пакет дополнения включает в себя 2 виджета:

- Войти с помощью - обеспечивает вход/регистрацию пользователей через популярные социальные сети и порталы;
- Мои аккаунты - позволяет пользователю просматривать подключенные аккаунты соцсетей, добавлять новые.

Установите эти виджеты в панели управления на странице Виджеты.

Каждый виджет может иметь своё значение uLogin ID, отличное от настроек в компонентах.



## Список изменений

#### 2.0.7

- Исправлено: все статические файлы и получение данных пользователей производятся по https-протоколу

#### 2.0.6

- Исправлено: ошибка с добавлением пользователей в выбранную группу

#### 2.0.5

- Исправлено: проблема с получением настроек из личного кабинета

#### 2.0.4

- Добавлен запрос photo,photo_big для получения аватары пользователя

#### 2.0.3

- Исправление ошибок

#### 2.0.2

- Добавлена поддержка размеров аватар, установленных в компоненте "Загрузка изображений"

#### 2.0.1

- При повторной установке пакета записи в таблицах не дублируются
- Незначительные изменения в алгоритме работы