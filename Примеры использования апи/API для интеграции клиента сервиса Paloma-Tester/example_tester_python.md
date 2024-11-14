#  Примеры запросов для API для интеграции клиента сервиса Paloma-Tester на Python
##  Запрос списка торговых точек
```python
import requests

host = "http://api.paloma365.com"
authkey = ""
url = f"{host}/company/api/?method=points&class=Tester&authkey={authkey}"

response = requests.request("GET", url)

print(response.text)
```
Ответ
```json
[
    {
        "point_id": 1, // id торговой точки нужен для отправки заказа на конкретную точку
        "name": "Мой тестовый аккаунт", // название торговой точки для отображения на стороне сайта если необходимо
        "address": "Алматы, Жибек Жолы 186" // адрес торговой точки
    }
]
```
##  Запрос меню
```python
import requests

host = "http://api.paloma365.com"
authkey = ""
url = f"{host}/company/api/?method=menu&class=Tester&authkey={authkey}"

response = requests.request("GET", url)

print(response.text)
```
Ответ
```json
{
    "item_groups": [
        {
            "object_id": 513, // id группы товаров 
            "name": "Горячие блюда", // название группы товаров 
            "parent_id": null, // если null или 0 то эта папка должны быть в корне, если содержит число то это id другой папки внутри которой она находится например есть папка Меню и внутри нее Напитки
            "items": [ // массив товаров находящихся в этой папке
                {
                    "object_id": 22, // id товара 
                    "name": "Лагман*", // наименование товара
                    "description": "Продукты (на 6 порций)\nГовядина - 600 г\nКартофель - 200 г\nПерец болгарский - 50 г\nМорковь - 80 г\nРедька (по желанию) - 50 г\nЛук репчатый - 50 г\nЧеснок - 10 г (2-3 зубчика)\nТомат-паста - 40 г\nили свежие помидоры - 3-4 шт.\nПерец молотый (черный и красный) - 0,25 ч. ложки (по вкусу)\nСоль - по вкусу\nБульон мясной или вода - 1 л (по вкусу)\nМасло растительное (или жир) - 30 г\nЗелень (для подачи) - 2 ст. ложки (по вкусу)\n*\nДля теста:\nМука пшеничная - 300 г\nЯйца - 2 шт.\nВода - 100 г", // описание товара
                    "mark_deleted": 0, // признак удален ли элемент если 1 то удален
                    "i_useInMenu": 1, // признак разрешено ли продавать этот товар
                    "article": "А-Блюдо-10022", // артикул 
                    "price": 2000, // цена продажи товара
                    "quantity": 0, // кол-во остатка товара
                    "image": "https://www.gorodtaraz.kz/upload/000/u1/08/32/lagman-photo-normal.jpg", // ссылка на изображение товара
                    "edit_date": "2023-04-05 14:31:19", // дата последнего изменения
                    "modifier_groups": [ // список модификаторов 
                        {
                            "object_id": 9, // id модификатора 
                            "name": "Добавить Чеснок", // наименование модификатора
                            "modifiers": [ // список товаров которые будут списаны по учету 
                                {
                                    "object_id": 101835, // id товара 
                                    "name": "чеснок", // наименование товара
                                    "mark_deleted": 0, // признак удален ли элемент если 1 то удален
                                    "i_useInMenu": 1,  // признак разрешено ли продавать этот товар
                                    "price": 1, // цена продажи товара
                                    "image": null, // ссылка на изображение товара
                                    "min_count": 0.01, // мин кол-во для выбора
                                    "max_count": 0.01 // макс кол-во для выбора
                                }
                            ]
                        }
                    ],
                    "complex_groups": [] // список товаров которые входят в состав товара применяется если товар является комбо набором
                }
            ]
        },
        {
            "object_id": 518,
            "name": "Напитки",
            "parent_id": null,
            "items": [
                {
                    "object_id": 6423,
                    "name": "Вода обычная без газа",
                    "description": "вода бонаква \nнормальная свежая",
                    "mark_deleted": 0,
                    "i_useInMenu": 1,
                    "article": "А-Вода",
                    "price": 500,
                    "quantity": 0,
                    "image": "https://storage.googleapis.com/paloma-project.appspot.com/image/smp/16516408294490.jpg",
                    "edit_date": "2023-04-04 10:11:37",
                    "modifier_groups": [],
                    "complex_groups": []
                }
            ]
        }
    ]
}
```
##  Запрос товаров в Стоп-листе
```python
import requests

host = "http://api.paloma365.com"
authkey = ""
point_id = 1 # point_id из запроса торговых точек параметр 
url = f"{host}/company/api/?class=Tester&method=stoplist&point_id={point_id}&authkey={authkey}"

response = requests.request("GET", url)

print(response.text)
```
Ответ
```json
{
    "point_id": 1, //  // id торговой точк
    "items": [ // список id товаров которые находятся в стоп листе 
        {
            "object_id": 101881
        }
    ]
}
```
##  Передача заказа
```python
import requests

host = "http://api.paloma365.com"
authkey = ""
point_id = 1 # point_id из запроса торговых точек параметр 
url = f"{host}/company/api/?method=order&class=Tester&point_id={point_id}&authkey={authkey}"

payload = json.dumps({
  "order_id": "20230206", // id заказа генерируется на вашей стороне должен быть уникальным
  "date": "2023-02-06 11:01:01", // дата заказа
  "name": "Илья", // имя клиента 
  "phone": "+77777777777", // телефон клиента 
  "email": "test@mail.ru", // email клиента 
  "address": "г. Алматы, ул. Абая, д. 180", // адрес клиента
  "coordinate_long": "37.6537388", // координаты 
  "coordinate_lat": "55.8783675",
  "comment": "Без лука", // коментарий к заказу отображается на кухне например если нужно указать алергию или другие пожелания или информацию 
  "person_amount": 1, // кол-во гостей для расчета приборов и статистики
  "total_price": 1000, // итоговая сумма заказа с учетом скидки 
  "discount_amount": 0, // сумма скидки 
  "exchange": 0,
  "delivery_type": 1, // тип заказа 1 доставка 0 самовывоз
  "is_cash": True, // тип оплаты заказа если True то наличные если нет то картой 
  "is_payed": True, // оплачен ли заказ
  "order_items": [ // список товаров в заказе 
    {
      "object_id": 32,  // id товара 
      "name": "Гамбургер", // наименование товара 
      "count": 1, // кол-во 
      "price": 1000 // цена 
    }
  ]
})

response = requests.request("POST", url, data=payload)

print(response.text)
```
Ответ
```json
{
    "order_id": "20230407", // id заказа в вашей системе
    "paloma_order_id": 1680849534, // id заказа в Paloma365
    "receipt_id": null, // id счета созданного на основании заказа
    "status": "new" //null/0 - new, 1 - cooking, 2 - ready, 3 - delivering, 4 - canceled, 5 - take out, 6 - delivered
}
```
##  Запрос Статуса заказа
```python
import requests

host = "http://api.paloma365.com"
authkey = ""
order_id = 1 # order_id из запроса Передача заказа
url = f"{host}/company/api/?method=status&class=Tester&order_id={order_id}&authkey={authkey}"

response = requests.request("GET", url)

print(response.text)
```
Ответ
```json
{
    "order_id": "20230407", // id заказа в вашей системе
    "paloma_order_id": 1675662704, // id заказа в Paloma365
    "receipt_id": 26, // id счета созданного на основании заказа
    "status": "new"  //null/0 - new, 1 - cooking, 2 - ready, 3 - delivering, 4 - canceled, 5 - take out, 6 - delivered
}
```
