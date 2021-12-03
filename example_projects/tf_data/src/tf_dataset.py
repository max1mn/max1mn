import glob
import h5py
import math

import os
# os.environ['TF_CPP_MIN_LOG_LEVEL'] = '1'

import tensorflow as tf

class TFDataset():

    def __init__(self, h5_dir, is_predict_mode, dataset_split=1):
        self.h5_dir = h5_dir
        self.is_predict_mode = is_predict_mode
        self.dataset_split = dataset_split

        if is_predict_mode:
            assert dataset_split == 1, 'dataset_split must be 1 in predict mode!'
        else:
            assert 0 < dataset_split <= 1, 'dataset_split must be in interval (0,1]!'

        self.output_types = None
        self.output_shapes = None

        # for splitting dataset only
        self.appx_data_len = 0

        # init output_types output_shapes
        self.load_shape_type()

    def get_filenames(self):
        return sorted(glob.glob(os.path.join(self.h5_dir, 'predict-*.h5' if self.is_predict_mode else 'train-*.h5')))

    def load_shape_type(self):

        # function loads type and shape
        def read_h5_recursive(output_types, output_shapes, appx_data_len, h5_group, group_name=''):
            # all keys
            for key in list(h5_group.keys()):
                if isinstance(h5_group[key], h5py.Group):
                    # group
                    (output_types, output_shapes, appx_data_len) = read_h5_recursive(output_types, output_shapes, appx_data_len, h5_group[key], r'{}{}/'.format(group_name, key))
                elif isinstance(h5_group[key], h5py.Dataset):
                    if appx_data_len == 0:
                        appx_data_len = h5_group[key].shape[0]
                    # dataset
                    output_types['{}{}'.format(group_name, key)] = h5_group[key].attrs['dtype']
                    output_shapes['{}{}'.format(group_name, key)] = h5_group[key].attrs['shape']

            return (output_types, output_shapes, appx_data_len)

        # load first file
        h5_filename = self.get_filenames()[0]

        appx_data_len = 0

        #h5_data
        output_types, output_shapes = dict(), dict()

        # read h5 file here
        with h5py.File(h5_filename, mode='r') as h5_file:
            # read recursive
            (output_types, output_shapes, appx_data_len) = read_h5_recursive(output_types, output_shapes, appx_data_len, h5_file)

        self.appx_data_len = appx_data_len

        # pop result
        output_types_result = output_types.pop('result')
        output_shapes_result = output_shapes.pop('result')

        self.output_types = (output_types, output_types_result)
        self.output_shapes = (output_shapes, output_shapes_result)

    # TODO - tf 2.5: deprecated output_types, output_shapes
    def load_output_signature(self):
        pass

    def val_generator_func(self):
        yield from self._generator_func(is_val=1)

    def generator_func(self):
        yield from self._generator_func(is_val=0)

    def _generator_func(self, is_val=0):

        # function loads data
        def read_h5_recursive(current_data, h5_group, group_name=''):
            # all keys
            for key in list(h5_group.keys()):
                if isinstance(h5_group[key], h5py.Group):
                    # group
                    current_data = read_h5_recursive(current_data, h5_group[key], r'{}{}/'.format(group_name, key))
                elif isinstance(h5_group[key], h5py.Dataset):
                    # dataset
                    current_data['{}{}'.format(group_name, key)] = h5_group[key][()]

            return current_data

        # dataset_split
        start_pos = 0
        end_pos = 0
        if self.dataset_split < 1:
            if is_val:
                # validation
                start_pos = math.ceil(self.appx_data_len * self.dataset_split)
                end_pos = 0
            else:
                # train
                start_pos = 0
                end_pos = math.ceil(self.appx_data_len * self.dataset_split)

        # counter
        count_dataset = 0

        # LOOP FILES BEGIN
        for h5_filename in self.get_filenames():

            #h5_data
            current_data = dict()

            # read h5 file here
            with h5py.File(h5_filename, mode='r') as h5_file:
                # read recursive
                current_data = read_h5_recursive(current_data, h5_file)

            # get length
            data_len = current_data[list(current_data.keys())[0]].shape[0]

            # result
            current_result = current_data.pop('result')

            # loop each data event
            for current_event_id in range(data_len):

                # counter
                count_dataset += 1

                if start_pos and count_dataset <= start_pos:
                    # start position
                    continue
                elif end_pos and count_dataset > end_pos:
                    # end position
                    continue

                # input
                current_input_dict = dict()
                for input_name in current_data:
                    current_input_dict[input_name] = current_data[input_name][current_event_id]

                yield (current_input_dict, current_result[current_event_id])

    def create_tensorflow_dataset(self):
        return tf.data.Dataset.from_generator(
            self.generator_func,
            output_types=self.output_types,
            output_shapes=self.output_shapes,
        )

    def create_tensorflow_val_dataset(self):
        return tf.data.Dataset.from_generator(
            self.val_generator_func,
            output_types=self.output_types,
            output_shapes=self.output_shapes,
        )
