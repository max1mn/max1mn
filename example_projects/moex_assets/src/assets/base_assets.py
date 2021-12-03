class AssetBase():
    """Base asset class
    """

    def __init__(self, asset_name: str, market_type: str, pip_price: float = 1.0, *args, **kwargs) -> None:
        """
        Constructor for AssetBase
            asset_name - name of asset, eg. GAZP
            market_type - stock, futures, option
        """
        super().__init__(*args, **kwargs)

        self.asset_name = asset_name
        self.pip_price = pip_price

        assert market_type in ['stock', 'futures']
        self.market_type = market_type

class AssetFutures(AssetBase):
    """
        Base futures class
    """

    def __init__(self, *args, **kwargs) -> None:
        """
        """
        super().__init__(market_type='futures', *args, **kwargs)
