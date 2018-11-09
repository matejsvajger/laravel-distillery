<?php

Route::match(['get', 'post'], '/{model}', 'DistilleryController@boil');
