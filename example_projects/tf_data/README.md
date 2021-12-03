## Deep network input pipeline

This is input pipeline for deep time-series analysing neural network.  

* Input: flat csv files 
* Step 1: transformation of flat input to hierarchical structure 
* Step 2: tf.Data dataset for model

The model is deep supervised neural network, programmed in tensorflow framework. Model predicts outcome of 12 
concurrent events, each event inputs are:

Input|Dimensionality|Description
--- |---|---
1   | (time window x 511)  | time series with 511 features (211 - numeric, 300 - word2vec comments from pretrained gensim model)
2-4 | (time window x 1)    | one-hot-encoded embeddings
5-7 | (400)         | statistical features with no time dimension (current state)

As number of events is 12, total number of inputs is 7x12 along with additional input - masking of 'disabled' events
(if some events are empty in particular experiment).

Module [dataset.py](https://github.com/max1mn/max1mn/blob/master/example_projects/tf_data/src/dataset.py)
is responsible for loading flat data and transforming it to hierarchical structure. Data loading steps in `load_data()`:
```
Data loading steps:
    * self._do_read_raw() - read raw csv file
    * self._do_prepare_raw() - engineer new features
    * self._do_drop_incomplete() - drop incomplete data
    * self._do_onehot_encode() - make one-hot-encoding
    * self._do_scale() - scale and save scaler for future online use
    * self._do_transform() - transform flat to hierarchical structure
```

Source code for last step of transformation `_do_transform()` is included.

Hierarchical data is cached to HDF5 binary file format (omitted) and then fed to Tensorflow tf.data API pipeline input
[tf_dataset.py](https://github.com/max1mn/max1mn/blob/master/example_projects/tf_data/src/tf_dataset.py)