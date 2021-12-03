from datetime import date
from dateutil.relativedelta import relativedelta, TH
from typing import ClassVar, Dict


class MoexExchange():
    """
        MOEX stock exchange
    """

    _expiration_schemes : ClassVar[Dict[str, relativedelta]] = {'3_thursday': relativedelta(weekday=TH(3))}

    def __init__(self, expiration_scheme: str, *args, **kwargs):
        super().__init__(*args, **kwargs)

        assert expiration_scheme in self._expiration_schemes.keys()
        self.expiration_scheme = expiration_scheme

    def _get_scheme_exp_date(self, year: int, month: int) -> date:
        return date(year, month, 1)+self._expiration_schemes[self.expiration_scheme]
