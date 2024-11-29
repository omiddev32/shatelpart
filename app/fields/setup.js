import { selectPlusFieldSetup } from "./SelectPlus/js/field"
import { translatableFieldSetup } from "./Translatable/js/field"
import { jsonTagsFieldSetup } from "./JsonTags/js/field"
import { persianNumberFieldSetup } from "./PersianNumber/js/field"
import { persianDateFieldSetup } from "./PersianDate/js/field"
import { ajaxSelectFieldSetup } from "./AjaxSelect/js/field"
import { tabsFieldSetup } from "./Tabs/js/field"

const registerCustomeFields = (app) => {
  selectPlusFieldSetup(app)
  translatableFieldSetup(app)
  jsonTagsFieldSetup(app)
  persianNumberFieldSetup(app)
  persianDateFieldSetup(app)
  ajaxSelectFieldSetup(app)
  tabsFieldSetup(app)
}

export { registerCustomeFields }