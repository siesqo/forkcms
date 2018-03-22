/* Bootstrap imports */
import 'bootstrap/dist/js/bootstrap'

/* Utilities imports */
import { BackToTop } from './Utilities/BackToTop'
import SweetScroll from 'sweet-scroll'
import { Resize } from './Utilities/Resize'
/* import {Fancybox} from './Utilities/Fancybox' */

/* Theme imports */
/* eg. import tooltip from './Theme/Tooltip' */
import { Pagination } from './Theme/Pagination'
import { FormBuilder } from './Theme/FormBuilder'

/* Renders */
window.sweetscroll = new SweetScroll()
window.resizeFunction = new Resize()
window.pagination = new Pagination()
window.formBuilder = new FormBuilder()

window.resizeFunction.resize()
window.pagination.events()
