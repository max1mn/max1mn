import unittest
from datetime import datetime, date

from src.assets import moex_assets


class TestMoexFuturesContinous(unittest.TestCase):
    def setUp(self):
        self.asset = moex_assets.MoexFuturesContinous(asset_name='gazr')

    def test__get_scheme_exp_date(self):
        self.assertEqual(self.asset._get_scheme_exp_date(2021, 9), date(2021, 9, 16))

    def test__get_asset_exp_dates(self):
        asset_exp_dates = self.asset._get_asset_exp_dates(datetime(2021, 9, 1))
        for exp_date in [date(2020, 12, 17),
                         date(2021, 3, 18),
                         date(2021, 6, 17),
                         date(2021, 9, 16),
                         date(2021, 12, 16),
                         date(2022, 3, 17)]:
            self.assertIn(exp_date, asset_exp_dates)

    def test_next_exp_date(self):
        self.assertEqual(self.asset.next_exp_date(datetime(2021, 8, 31)), date(2021, 9, 16))
        self.assertEqual(self.asset.next_exp_date(datetime(2021, 9, 1)),  date(2021, 9, 16))
        self.assertEqual(self.asset.next_exp_date(datetime(2021, 9, 16)), date(2021, 9, 16))
        self.assertEqual(self.asset.next_exp_date(datetime(2021, 9, 17)), date(2021, 12, 16))
        self.assertEqual(self.asset.next_exp_date(datetime(2021, 10, 1)), date(2021, 12, 16))
        self.assertEqual(self.asset.next_exp_date(datetime(2021, 12, 31)), date(2022, 3, 17))

    def test_prev_exp_date(self):
        self.assertEqual(self.asset.prev_exp_date(datetime(2021, 10, 1)), date(2021, 9, 16))
        self.assertEqual(self.asset.prev_exp_date(datetime(2021, 9, 30)), date(2021, 9, 16))
        self.assertEqual(self.asset.prev_exp_date(datetime(2021, 9, 17)), date(2021, 9, 16))
        self.assertEqual(self.asset.prev_exp_date(datetime(2021, 9, 16)), date(2021, 6, 17))
        self.assertEqual(self.asset.prev_exp_date(datetime(2021, 8, 31)), date(2021, 6, 17))
        self.assertEqual(self.asset.prev_exp_date(datetime(2021, 1, 1)), date(2020, 12, 17))

    def test_days_next_exp_date(self):
        self.assertEqual(self.asset.days_next_exp_date(datetime(2021, 8, 31)), 16)
        self.assertEqual(self.asset.days_next_exp_date(datetime(2021, 9, 16)), 0)

    def test_days_prev_exp_date(self):
        self.assertEqual(self.asset.days_prev_exp_date(datetime(2021, 9, 17)), 1)
        self.assertEqual(self.asset.days_prev_exp_date(datetime(2021, 9, 16)), 91)


if __name__ == '__main__':
    unittest.main()
