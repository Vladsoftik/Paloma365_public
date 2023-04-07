#  Примеры запросов на Python
## Запрос товаров
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