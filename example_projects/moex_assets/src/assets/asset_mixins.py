from abc import ABCMeta, abstractmethod
from datetime import datetime, date

from typing import List, Union


class AssetContinousExpiration(metaclass=ABCMeta):
    """
    """

    def __init__(self, expiration_months: List[int], *args, **kwargs):
        super().__init__(*args, **kwargs)

        self.expiration_months = expiration_months
        # cache exp_dates
        self._cache_exp_dates = {}

    @abstractmethod
    def _get_scheme_exp_date(self, year: int, month: int) -> date:
        pass

    def _get_asset_exp_dates(self, fromdatetime: Union[datetime, date]) -> List[date]:
        fromdate = date(fromdatetime.year, fromdatetime.month, fromdatetime.day)
        if fromdate.year in self._cache_exp_dates:
            return self._cache_exp_dates[fromdate.year]
        else:
            exp_dates = list()
            exp_dates.extend([self._get_scheme_exp_date(fromdate.year - 1, month) for month in self.expiration_months])
            exp_dates.extend([self._get_scheme_exp_date(fromdate.year, month) for month in self.expiration_months])
            exp_dates.extend([self._get_scheme_exp_date(fromdate.year + 1, month) for month in self.expiration_months])

            self._cache_exp_dates.update({fromdate.year: exp_dates})

        return self._cache_exp_dates[fromdate.year]

    def next_exp_date(self, fromdatetime: Union[datetime, date]) -> date:
        fromdate = date(fromdatetime.year, fromdatetime.month, fromdatetime.day)
        return min([x for x in self._get_asset_exp_dates(fromdate) if x >= fromdate])

    def prev_exp_date(self, fromdatetime: Union[datetime, date]) -> date:
        fromdate = date(fromdatetime.year, fromdatetime.month, fromdatetime.day)
        return max([x for x in self._get_asset_exp_dates(fromdate) if x < fromdate])

    def days_next_exp_date(self, fromdatetime: Union[datetime, date]) -> int:
        fromdate = date(fromdatetime.year, fromdatetime.month, fromdatetime.day)
        return (self.next_exp_date(fromdate) - fromdate).days

    def days_prev_exp_date(self, fromdatetime: Union[datetime, date]) -> int:
        fromdate = date(fromdatetime.year, fromdatetime.month, fromdatetime.day)
        return (fromdate - self.prev_exp_date(fromdate)).days
