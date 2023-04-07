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
        "point_id": 1,
        "name": "Мой тестовый аккаунт",
        "address": "Алматы, Жибек Жолы 186"
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
            "object_id": 513,
            "name": "Горячие блюда",
            "parent_id": null,
            "items": [
                {
                    "object_id": 22,
                    "name": "Лагман*",
                    "description": "Продукты (на 6 порций)\nГовядина - 600 г\nКартофель - 200 г\nПерец болгарский - 50 г\nМорковь - 80 г\nРедька (по желанию) - 50 г\nЛук репчатый - 50 г\nЧеснок - 10 г (2-3 зубчика)\nТомат-паста - 40 г\nили свежие помидоры - 3-4 шт.\nПерец молотый (черный и красный) - 0,25 ч. ложки (по вкусу)\nСоль - по вкусу\nБульон мясной или вода - 1 л (по вкусу)\nМасло растительное (или жир) - 30 г\nЗелень (для подачи) - 2 ст. ложки (по вкусу)\n*\nДля теста:\nМука пшеничная - 300 г\nЯйца - 2 шт.\nВода - 100 г",
                    "mark_deleted": 0,
                    "i_useInMenu": 1,
                    "article": "А-Блюдо-10022",
                    "price": 2000,
                    "quantity": 0,
                    "image": "https://www.gorodtaraz.kz/upload/000/u1/08/32/lagman-photo-normal.jpg",
                    "edit_date": "2023-04-05 14:31:19",
                    "modifier_groups": [
                        {
                            "object_id": 9,
                            "name": "Добавить Чеснок",
                            "modifiers": [
                                {
                                    "object_id": 101835,
                                    "name": "чеснок",
                                    "mark_deleted": 0,
                                    "i_useInMenu": 1,
                                    "price": 1,
                                    "image": null,
                                    "min_count": 0.01,
                                    "max_count": 0.01
                                }
                            ]
                        }
                    ],
                    "complex_groups": []
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
    "point_id": 1,
    "items": [
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
  "order_id": "20230206",
  "date": "2023-02-06 11:01:01",
  "name": "Илья",
  "phone": "+77777777777",
  "email": "test@mail.ru",
  "address": "г. Алматы, ул. Абая, д. 180",
  "coordinate_long": "37.6537388",
  "coordinate_lat": "55.8783675",
  "comment": "Без лука",
  "person_amount": 1,
  "total_price": 1000,
  "discount_amount": 0,
  "exchange": 0,
  "delivery_type": 1,
  "is_cash": True,
  "is_payed": True,
  "order_items": [
    {
      "object_id": 32,
      "name": "Гамбургер",
      "count": 1,
      "price": 1000
    }
  ]
})

response = requests.request("POST", url, data=payload)

print(response.text)
```
Ответ
```json
{
    "order_id": "20230407",
    "paloma_order_id": 1680849534,
    "receipt_id": null,
    "status": "new"
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
    "order_id": "20230407",
    "paloma_order_id": 1675662704,
    "receipt_id": 26,
    "status": "new" 
}
```