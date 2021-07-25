/* Bootstrap imports */
import 'bootstrap'
import '@fortawesome/fontawesome-free/js/all'

/* Utilities imports */
import { BackToTop } from './Utilities/BackToTop'
import { ScrollTo } from './Utilities/ScrollTo'
import { Resize } from './Utilities/Resize'
/* import {Fancybox} from './Utilities/Fancybox' */

/* Theme imports */
/* eg. import tooltip from './Theme/Tooltip' */
import { Pagination } from './Theme/Pagination'
import { FormBuilder } from './Theme/FormBuilder'

/* Renders */
window.scrollto = new ScrollTo()
window.resizeFunction = new Resize()
window.pagination = new Pagination()
window.backToTop = new BackToTop()
window.formBuilder = new FormBuilder()

window.resizeFunction.resize()
window.pagination.events()
