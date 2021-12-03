from .base_assets import AssetFutures
from .asset_mixins import AssetContinousExpiration
from .exchange_mixins import MoexExchange


class MoexFuturesContinous(AssetFutures, MoexExchange, AssetContinousExpiration):
    """
        MOEX synthetic continuous 3 month futures (stock, index, currency, brent)
    """

    def __init__(self, *args, **kwargs):
        """
        """
        super().__init__(expiration_months=[3, 6, 9, 12], expiration_scheme='3_thursday',
                         *args, **kwargs)

MoexFuturesGazprom = MoexFuturesContinous(asset_name='GAZR', pip_price=1.0)
MoexFuturesBrent = MoexFuturesContinous(asset_name='BR', pip_price=0.01)
MoexFuturesLukoil = MoexFuturesContinous(asset_name='LKOH', pip_price=1.0)

