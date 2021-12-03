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
