#  Примеры запросов для API сервиса Paloma-1C на Python

## Описание таблиц
### Справочники 
- s_items / Товары и услуги
- s_shtrih / Дополнительные штрихкоды
- s_clients / Контрагенты 
- s_type_inout / Статья движения денежных средств
- s_warehouse / Склады
- s_organizations / Организации
- s_cash / Кассы
- s_bank  / Банковские счета
- s_employee / Сотрудники
- s_location / Помещения 
- s_objects / Столы
- s_menu / Меню
- t_menu_items / Товары в меню
- s_nds_type / Налоговые ставки 
- s_prices_types / Типы цен
- s_position / Должности
- s_types_of_payment / Виды оплаты 
- s_units_of_measurement / Единицы измерения


### Документы 
- d_order / Счет заказ
- d_receipt / Поступление товара
- d_selling / Реализация товара
- d_posting / Оприходование
- d_cancellation / Списание
- d_inventory / Инвентаризация 
- d_movement / Перемещение между складами
- d_return_from_covenantee / Возврат от покупателя
- d_return_to_covenantee / Возврат поставщику
- d_regrading / Пересортица
- d_production / Выпуск продукции
- d_stripping / Акт разделки
- d_setunset / Акт комплектации
- d_cash_income / Поступление в кассу
- d_cash_outcome / Расход из кассы
- d_cash_movement / Перемещение между кассами
- d_cash_bank_income / Перемещение c банковского счета в кассу
- d_cash_bank_outcome / Перемещение из кассы на банковский счет
- d_bank_income / Поступление в банковский счет
- d_bank_outcome / Расход из банковского счета
- d_bank_movement / Перемещение между банковскими счетами
- d_extra_charge / Наценка на товаары и услуги
- r_prices_types / Установка цены на товар
- d_urv_transactions / Отметки времени прихода ухода в Учете рабочего времени

## Запрос Товаров и услуг
```python
import requests

host = "http://api.paloma365.com"
authkey = ""
url = f"{host}/company/api/?class=guide2xml&method=to_file&tables[0]=s_items&output_format=json&authkey={authkey}"

response = requests.request("GET", url)

print(response.text)
```
Ответ
```json
{
  "s_items": {
    "100248": {
      "articul": "АСладкое",
      "categoryid": "{139AC109-668B-3A0F-AD00-E46A95F1D976}",
      "clientid": 0,
      "complex": "0",
      "cook_time": "NULL",
      "cook_type": 0,
      "critical_max": "NULL",
      "critical_min": "NULL",
      "description": "10 жевательных конфет с фруктовым вкусом",
      "description2": "NULL",
      "i_printer": 0,
      "i_useInMenu": "0",
      "idlink": "A00000003",
      "idout": "01001335",
      "isgroup": "0",
      "isservice": "0",
      "lastinprice": "0.000",
      "loss_cold": "0.00000",
      "loss_hot": "0.00000",
      "mainShtrih": "2000000000025",
      "measurement": "{5541CE61-1C6F-69FB-9DFB-6D632447EAEB}",
      "name": "Fruitella Pixel уп 800г",
      "nds_type_id": 0,
      "need_marking": "0",
      "parentid": "{38AF8E7A-F1A0-53CD-3D71-408FB6079DB9}",
      "PLU": "NULL",
      "price": "1500.00000",
      "UID": "{2A01280B-D3B4-2BAE-FFDB-B59D76C2B9D7}",
      "weight": "0"
    }
  }
}
```
## Запрос Контрагентов 
```python
import requests

host = "http://api.paloma365.com"
authkey = ""
url = f"{host}/company/api/?class=guide2xml&method=to_file&tables[0]=s_clients&output_format=json&authkey={authkey}"

response = requests.request("GET", url)

print(response.text)
```
Ответ
```json
{
  "s_clients": {
        "3": {
            "UID": "{38B2E43F-240E-6114-6E05-03EBD12FB3C6}",
            "isgroup": "1",
            "parentid": 0,
            "idlink": "P3",
            "idout": "00000002",
            "name": "Поставщики",
            "fullname": "NULL",
            "shtrih": "NULL",
            "birthday": "NULL",
            "email": "NULL",
            "phone": "NULL",
            "marketing_src": 0,
            "rnn": "NULL",
            "iikbank": "NULL",
            "beneficiary_bank": "NULL",
            "BIK": "NULL",
            "address": "NULL",
            "details": "NULL",
            "info": "NULL",
            "dtedit": "2023-03-02 09:03:27"
        },
        "2350": {
            "UID": "{B8546A57-D715-6C66-2D13-C7F47317194E}",
            "isgroup": "0",
            "parentid": "{38B2E43F-240E-6114-6E05-03EBD12FB3C6}",
            "idlink": "NULL",
            "idout": "00000190",
            "name": "ТОО Прима Дистрибьюшн",
            "fullname": "NULL",
            "shtrih": "NULL",
            "birthday": "2016-11-01",
            "email": "NULL",
            "phone": "NULL",
            "marketing_src": 0,
            "rnn": "NULL",
            "iikbank": "NULL",
            "beneficiary_bank": "NULL",
            "BIK": "NULL",
            "address": "NULL",
            "details": "NULL",
            "info": "NULL",
            "dtedit": "2022-12-07 12:56:55"
        }
    }
}
```
## Запрос Розничных продаж 
```python
import requests

host = "http://api.paloma365.com"
authkey = ""
url = f"{host}/company/api/?class=xml_exchange_data%5CoutDocuments&method=to_file&tables%5B0%5D=d_order&output_format=json&chb=zaperiod&chb_zaperiod1=01.01.2023 0:00:00&chb_zaperiod2=10.01.2023 23:59:59&authkey={authkey}"

response = requests.request("GET", url)

print(response.text)
```
Ответ
```json
{
    "documents": {
        "d_order": {
            "rows": [
                {
                    "barcode": null,
                    "changeid": "{6B19DB05-99C3-F0FA-901C-8751DCEDBF00}",
                    "clientid": "{01F59CE6-71BA-3CF1-16D2-EAB63CBADE13}",
                    "closed": "1",
                    "conducted": "1",
                    "creationdt": "2022-09-20 12:53:31",
                    "discountid": "0",
                    "discountpercent": "0",
                    "discountsum": "0.00000",
                    "dtclose": "2022-10-06 09:50:58",
                    "emp_pay": "{E4B96430-A9D7-69FA-CDE2-B3FC9B9841EF}",
                    "employeeid": "{E4B96430-A9D7-69FA-CDE2-B3FC9B9841EF}",
                    "guestcount": "1",
                    "guid": "{A26BB542-2D58-4C15-989E-A7B6B614C924}",
                    "handservicepercent": "0",
                    "handservicesum": "0.00000",
                    "id": "1772",
                    "idautomated_point": "{24683247-178D-9386-1554-999D4F4DDE65}",
                    "idlink": "172022-09-20 12:53:31",
                    "idout": "1",
                    "interfaceid": "0",
                    "isgroup": "0",
                    "name": null,
                    "note": "",
                    "objectid": "{6AC1717A-678C-D3DF-4771-CC4FDEB81B1A}",
                    "parentid": "0",
                    "paymentid": "{69545690-28DB-875C-609F-1FBA3BDE8D78}",
                    "printed": "1",
                    "servicepercent": "0",
                    "servicesum": "0.00000",
                    "sumfromclient": "1500.00000",
                    "t_order": [
                        {
                            "complex": "0",
                            "coocked": "0",
                            "discountsum": "0.00000",
                            "dt": "2022-09-20 12:53:31",
                            "id": "4304",
                            "iddoc": null,
                            "idlink": "322022-09-20 12:53:31",
                            "idout": "32",
                            "isgroup": "0",
                            "itemid": "{1FDE2342-7DEF-E24D-E48D-107163D86FD1}",
                            "measure_id": "{5541CE61-1C6F-69FB-9DFB-6D632447EAEB}",
                            "name": null,
                            "note": "",
                            "orderid": "{A26BB542-2D58-4C15-989E-A7B6B614C924}",
                            "parentid": "0",
                            "price": "1500.00000",
                            "printed": "1",
                            "printerid": "{5D3A982B-1FC5-E1B6-5F74-E160B3B7C9F8}",
                            "quantity": "1.00000",
                            "salesum": "1500.00000",
                            "servicesum": "0.00000",
                            "specificationid": "0",
                            "sum": "1500.00000",
                            "UID": "322022-09-20 12:53:31",
                            "warehouseid": "{9E43E7E1-BED9-9E2E-A2DE-1470CD4D9D84}"
                        }
                    ],
                    "t_order_price_type": [
                    ],
                    "totalsum": "1500.00000",
                    "UID": "{A26BB542-2D58-4C15-989E-A7B6B614C924}",
                    "wpid": "2"
                }
            ]
        }
    }
}
```
## Запрос Документов Поступление товаров
```python
import requests

host = "http://api.paloma365.com"
authkey = ""
url = f"{host}/company/api/?class=xml_exchange_data%5CoutDocuments&method=to_file&tables%5B0%5D=d_receipt&output_format=json&chb=zaperiod&chb_zaperiod1=01.01.2023 0:00:00&chb_zaperiod2=10.01.2023 23:59:59&authkey={authkey}"

response = requests.request("GET", url)

print(response.text)
```
Ответ
```json
{
    "documents": {
        "d_receipt": {
            "rows": [
                {
                    "UID": "{60CE4B9E-95BE-8986-D15F-7C28D99E3862}",
                    "id": "208",
                    "idout": "1",
                    "isgroup": "0",
                    "dt": "2022-08-29 10:51:00",
                    "parentid": "0",
                    "organizationid": "{D659FCA7-FA40-ED06-9B46-38A8DE441E78}",
                    "idlink": "1",
                    "warehouseid": "{9E43E7E1-BED9-9E2E-A2DE-1470CD4D9D84}",
                    "clientid": "{9A7EEE5A-47AF-FCBD-9DA4-DC3D0F615986}",
                    "type_inout": "{8914E1F9-6FA1-75A8-C578-BBA9B289207A}",
                    "conducted": "1",
                    "total": "200",
                    "payd": null,
                    "extendmode": "1",
                    "pricetypeid": -1,
                    "employeeid": 0,
                    "note": "",
                    "invoice_no": "",
                    "invoice_contract": "",
                    "invoice_payment_terms": "",
                    "invoice_attorney": "",
                    "delivery_invoice": "",
                    "guid": "{60CE4B9E-95BE-8986-D15F-7C28D99E3862}",
                    "name": "",
                    "dtedit": "2023-04-04 09:33:31",
                    "t_receipt": [
                        {
                            "UID": "0000000000031999",
                            "id": "31999",
                            "documentid": "{60CE4B9E-95BE-8986-D15F-7C28D99E3862}",
                            "itemid": "{7A087401-02DA-D23F-0D8E-0F538139B530}",
                            "quantity": "1.00000",
                            "measureid": "{23F59D28-C02F-A3E8-858F-B515AE1C81C4}",
                            "multip": "1.00000",
                            "total": "200.000",
                            "markup": "900.00000",
                            "rounding": "100.00000",
                            "newprice": "2000.00000",
                            "idout": null,
                            "idlink": null,
                            "parentid": "0",
                            "isgroup": "0",
                            "dt": "0000-00-00 00:00:00",
                            "specificationid": "0",
                            "dtedit": "2022-08-31 10:49:51"
                        },
                        {
                            "UID": "0000000000032002",
                            "id": "32002",
                            "documentid": "{60CE4B9E-95BE-8986-D15F-7C28D99E3862}",
                            "itemid": "{8C31B377-DEBC-0407-7565-D901D54B5562}",
                            "quantity": "1.00000",
                            "measureid": "{5541CE61-1C6F-69FB-9DFB-6D632447EAEB}",
                            "multip": "1.00000",
                            "total": "0.000",
                            "markup": "0.00000",
                            "rounding": "1.00000",
                            "newprice": "0.00000",
                            "idout": null,
                            "idlink": null,
                            "parentid": "0",
                            "isgroup": "0",
                            "dt": "0000-00-00 00:00:00",
                            "specificationid": "0",
                            "dtedit": "2022-09-02 10:30:22"
                        }
                    ]
                }
            ]
        }
    }
}
```
