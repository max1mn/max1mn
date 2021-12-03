## MOEX (Moscow stock exchange) futures

MOEX continuous futures for backtesting of trading strategies. The asset in backtesting is synthetic, every 3 months
new real futures on stock market appears. We need to know:

* Expiration scheme (3-rd thursday for MOEX)
* Expiration months (3, 6, 9, 12 months of each year)
* Next and previous expiration dates
* How much time we have before/after expiration to stop trading
* Pip size (price step of asset)

Top level futures class 
[MoexFuturesContinous](https://github.com/max1mn/max1mn/blob/master/example_projects/moex_assets/src/assets/moex_assets.py) 
is constructed from 
[AssetBase](https://github.com/max1mn/max1mn/blob/master/example_projects/moex_assets/src/assets/base_assets.py) 
(base asset logic) 
with mixin classes 
[AssetContinousExpiration](https://github.com/max1mn/max1mn/blob/master/example_projects/moex_assets/src/assets/asset_mixins.py) 
(asset expiration logic) 
and 
[MoexExchange](https://github.com/max1mn/max1mn/blob/master/example_projects/moex_assets/src/assets/exchange_mixins.py)
(expiration scheme logic).

Use case: pause trading in backtesting 3 days before and 1 day after expiration. 

Vanilla example:

```python
from datetime import datetime
from src.assets.moex_assets import MoexFuturesGazprom, MoexFuturesBrent, MoexFuturesLukoil

# asset and current date
asset = MoexFuturesGazprom
today = datetime.now().date()

print('Today is {}'.format(today))
print('Asset {} next expiration date is {}'.format(asset.asset_name, asset.next_exp_date(today)))
print('Asset {} previous expiration date is {}'.format(asset.asset_name, asset.prev_exp_date(today)))
print('Asset {} expires in {} days'.format(asset.asset_name, asset.days_next_exp_date(today)))
print('Asset {} expired {} days ago'.format(asset.asset_name, asset.days_prev_exp_date(today)))
print('Asset {} price step is {}'.format(asset.asset_name, asset.pip_price))
```
Output:
```console
example_projects\moex_assets>python example.py
Today is 2021-10-13
Asset GAZR next expiration date is 2021-12-16
Asset GAZR previous expiration date is 2021-09-16
Asset GAZR expires in 64 days
Asset GAZR expired 27 days ago
Asset GAZR price step is 1.0
```

Testing is covered with [unittest](https://github.com/max1mn/max1mn/blob/master/example_projects/moex_assets/test/test_moex_assets.py).

```console
example_projects\moex_assets>python -m unittest      
......                                                                                   
----------------------------------------------------------------------                   
Ran 6 tests in 0.001s                                                                    
                                                                                         
OK
```