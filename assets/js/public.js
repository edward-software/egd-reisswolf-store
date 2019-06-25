'use strict';

import '../scss/public.scss';

global.$ = global.jQuery = require('jquery');

const moment = require('moment');
global.moment = moment;

import 'bootstrap';
import 'jquery-ui/ui/widgets/datepicker';
import 'jquery-ui/ui/i18n/datepicker-fr';
import 'jquery-timepicker/jquery.timepicker';
import 'eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min';
import 'blueimp-file-upload/js/jquery.fileupload';
import 'node-waves/dist/waves.min.js';
import '../../src/Paprec/PublicBundle/Resources/assets/js/public';

const Swal = require('sweetalert2');
global.Swal = Swal;
