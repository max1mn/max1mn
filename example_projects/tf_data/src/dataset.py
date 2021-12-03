import logging
import math
import numpy as np
import pandas as pd
import os

# import json
from sklearn.preprocessing import MinMaxScaler
import joblib

from tensorflow.keras.utils import to_categorical


class Dataset:

    def load_data(self, dataset_filename, need_init_scaler):
        """
        Data loading steps:
            * self._do_read_raw() - read raw csv file
            * self._do_prepare_raw() - engineer new features
            * self._do_drop_incomplete() - drop incomplete data
            * self._do_onehot_encode() - make one-hot-encoding
            * self._do_scale() - scale and save scaler for future online use
            * self._do_transform() - transform flat to hierarchical structure
        """

        # log
        logging.info('dataset.load_data START')

        #
        self.dataset_filename = dataset_filename

        # read data
        data_raw = self._do_read_raw()

        # read data
        data_raw = self._do_prepare_raw(data_raw)

        # 1 drop incomplete samples
        data_raw = self._do_drop_incomplete(data_raw)

        # sort and set index
        data_raw.sort_values(['event_id', 'post_position', 'runner_id', 'stepback_num'],
                             ascending=[True, True, True, False], axis=0, inplace=True)
        data_raw.set_index(['event_id', 'post_position', 'runner_id', 'stepback_num'], inplace=True)

        # ohe fields
        data_raw = self._do_onehot_encode(data_raw)

        # set input columns
        self._set_input_columns(data_raw.columns.tolist())

        # scale
        data_raw = self._do_scale(data_raw, need_init_scaler)

        # transform
        self.input_data, self.result_data, self.lookup_data = self._do_transform(data_raw,
                                                                                 data_raw.index.unique('event_id'))

        # log
        logging.info('dataset.load_data FINISH')

        return True

    def _do_transform(self, data_raw, unique_event_ids):

        # log
        logging.info('dataset._do_transform')

        # main
        if self._need_training_shuffle and not self.is_predict_mode:
            # shuffle unique_event_ids for training
            unique_event_ids = np.random.permutation(unique_event_ids)

        # create empty lists of inputs
        input_dict = dict()
        for cur_runner_num in range(self.num_of_runners):
            # main input
            input_dict[r'runner_{}/runs_data_input'.format(cur_runner_num)] = np.empty(len(unique_event_ids), dtype=object)

            # comments
            input_dict[r'runner_{}/runs_comment_input'.format(cur_runner_num)] = np.empty(len(unique_event_ids), dtype=object)

            # embeddings
            for emb_field_name in self.get_embedding_fields():
                input_dict[r'runner_{}/runs_{}_input'.format(cur_runner_num, emb_field_name)] = np.empty(len(unique_event_ids), dtype=object)

        # active_runners_mask
        input_dict[r'active_runners_mask_input'] = np.zeros((len(unique_event_ids), self.num_of_runners), dtype='float32')

        # current embeddings
        for emb_field_name in self.get_embedding_fields():
            input_dict[r'cur_{}_input'.format(emb_field_name)] = np.zeros((len(unique_event_ids), 1), dtype='int16')

        # empty result - numpy table of zeroes (num_of_runners x events_count) shape
        result_data = np.zeros((len(unique_event_ids), self.num_of_runners), dtype='int16')

        # runners data - runner id's
        lookup_data = pd.DataFrame(
            np.zeros((len(unique_event_ids), self.num_of_runners+1), dtype='int32'),
            index=range(len(unique_event_ids)), columns=['event_id']+['runner_{}'.format(x) for x in range(self.num_of_runners)])

        # index: (event_id, post_position, runner_id, stepback_num)
        for event_id_idx, event_id in enumerate(unique_event_ids):

            # post_position
            post_position_idx = 0

            # winner post position -  data is post_position aligned
            winner_post_position_idx = -1

            # save event_id
            lookup_data.loc[event_id_idx, 'event_id'] = event_id

            # slice
            event_id_slice = data_raw.xs(key=event_id, level='event_id') # new index=('post_position', 'runner_id', 'stepback_num')

            # current_values_list
            current_values_list = []

            for _, post_position in enumerate(event_id_slice.index.unique(level='post_position').tolist()):

                # slice
                post_position_slice = event_id_slice.xs(key=post_position, level='post_position') # new index=('runner_id', 'stepback_num')

                for _, runner_id in enumerate(post_position_slice.index.unique(level='runner_id').tolist()):

                    # runs_data and runs_comment inputs
                    runs_data_input = input_dict[r'runner_{}/runs_data_input'.format(post_position_idx)]
                    runs_comment_input = input_dict[r'runner_{}/runs_comment_input'.format(post_position_idx)]

                    # slice previously set column names
                    runner_id_slice = post_position_slice.xs(key=runner_id, level='runner_id') # new index=('stepback_num')

                    #
                    runs_data_input[event_id_idx] = runner_id_slice[self.input_columns]
                    runs_comment_input[event_id_idx] = runner_id_slice[self.comment_input_columns]

                    # future info column indexes
                    columns = runs_data_input[event_id_idx].columns
                    future_column_indices = [columns.get_loc(c) for c in self._get_future_info_fields() if c in columns]

                    columns = runs_comment_input[event_id_idx].columns
                    comment_future_column_indices = [columns.get_loc(c) for c in self._get_future_info_fields() if c in columns]

                    if self._need_shifting:
                        # shift future_info_fields down
                        runs_data_input[event_id_idx].iloc[:, future_column_indices] \
                            = runs_data_input[event_id_idx].iloc[:, future_column_indices].shift(1)
                        runs_data_input[event_id_idx].iloc[0, future_column_indices] \
                            = np.zeros(len(future_column_indices))

                        # comments
                        runs_comment_input[event_id_idx].iloc[:, comment_future_column_indices] \
                            = runs_comment_input[event_id_idx].iloc[:, comment_future_column_indices].shift(1)
                        runs_comment_input[event_id_idx].iloc[0, comment_future_column_indices] \
                            = np.zeros(len(future_column_indices))
                    else:
                        # for stepback=0 unset future_info_fields
                        runs_data_input[event_id_idx].iloc[-1, future_column_indices] = 0
                        runs_comment_input[event_id_idx].iloc[-1, comment_future_column_indices] = 0

                    # convert dataframe to numpy
                    runs_data_input[event_id_idx] = runs_data_input[event_id_idx].to_numpy(dtype='float32')
                    runs_comment_input[event_id_idx] = runs_comment_input[event_id_idx].to_numpy(dtype='float32')

                    # embeddings
                    for emb_field_name in self.get_embedding_fields():
                        input_dict[r'runner_{}/runs_{}_input'.format(post_position_idx, emb_field_name)][event_id_idx] \
                            = runner_id_slice[emb_field_name].to_numpy(dtype='int16')

                    # find runner's finish position in current race (stepback_num=0)
                    if runner_id_slice.loc[0, 'position'] == 1:
                        winner_post_position_idx = post_position_idx

                    # save runner_id
                    lookup_data.loc[event_id_idx, 'runner_{}'.format(post_position_idx)] = runner_id

                    # save current_values_list - runner #1 stepback=0
                    if not len(current_values_list):
                        for emb_field_name in self.get_embedding_fields():
                            current_values_list.append(runner_id_slice.loc[0, emb_field_name])

                    # iter
                    post_position_idx += 1

            # fill empty runners with 1 string of zeroes
            for empty_runner_post_position_idx in range(post_position_idx, self.num_of_runners):

                # runs_data and runs_comment inputs
                runs_data_input = input_dict[r'runner_{}/runs_data_input'.format(empty_runner_post_position_idx)]
                runs_comment_input = input_dict[r'runner_{}/runs_comment_input'.format(empty_runner_post_position_idx)]

                runs_data_input[event_id_idx] \
                    = np.zeros((1,len(self.input_columns)), dtype='float32')

                runs_comment_input[event_id_idx] \
                    = np.zeros((1,len(self.comment_input_columns)), dtype='float32')

                # embeddings
                for emb_field_name in self.get_embedding_fields():
                    input_dict[r'runner_{}/runs_{}_input'.format(empty_runner_post_position_idx, emb_field_name)][event_id_idx] \
                        = np.zeros(1, dtype='int16')

            # active_runners_mask
            input_dict[r'active_runners_mask_input'][event_id_idx, 0:post_position_idx] = 1

            # check we have curent values
            if not len(current_values_list):
                raise Exception('no_current_values', 'event_id: '+event_id)

            # save current values
            for emb_index, emb_field_name in enumerate(self.get_embedding_fields()):
                input_dict[r'cur_{}_input'.format(emb_field_name)][event_id_idx] \
                    = current_values_list[emb_index]

            # check we have winner
            if winner_post_position_idx < 0:
                raise Exception('no_winner', 'event_id: '+event_id)

            # event result - vector of length self.num_of_runners
            result_data[event_id_idx][winner_post_position_idx] = 1

        # return result
        return (input_dict, result_data, lookup_data)

    def __init__(self):
        """<Source code skipped>"""
        pass

    def _do_read_raw(self):
        """<Source code skipped>"""
        pass

    def _do_prepare_raw(self, data_raw):
        """<Source code skipped>"""
        pass

    def _do_drop_incomplete(self, data_raw):
        """<Source code skipped>"""
        pass

    def _do_onehot_encode(self, data_raw):
        """<Source code skipped>"""
        pass

    def _do_scale(self, data_raw, need_scaler_fit):
        """<Source code skipped>"""
        pass

    def _set_input_columns(self, i_all_columns_list):
        """<Source code skipped>"""
        pass

    def _get_future_info_fields(self):
        """<Source code skipped>"""
        pass

    def _get_onehot_fields(self):
        """<Source code skipped>"""
        pass

    def get_embedding_fields(self):
        """<Source code skipped>"""
        pass

    def _get_drop_fields(self):
        """<Source code skipped>"""
        pass

    def _filename_append_suffix(self, i_filename, suffix):
        dir_name, file_name = os.path.split(i_filename)
        file_name, file_ext = os.path.splitext(file_name)
        return os.path.join(dir_name, file_name + '_' + suffix + file_ext)

    def _filename_append_prefix(self, i_filename, suffix):
        dir_name, file_name = os.path.split(i_filename)
        file_name, file_ext = os.path.splitext(file_name)
        return os.path.join(dir_name, suffix + '_' + file_name + file_ext)

