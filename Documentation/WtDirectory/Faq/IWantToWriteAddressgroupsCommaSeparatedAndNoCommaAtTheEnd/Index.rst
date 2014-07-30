

.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. ==================================================
.. DEFINE SOME TEXTROLES
.. --------------------------------------------------
.. role::   underline
.. role::   typoscript(code)
.. role::   ts(typoscript)
   :class:  typoscript
.. role::   php(code)


I want to write addressgroups comma separated and no comma at the end!?
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

- Constants:

\# seperate address groups with comma

plugin.wtdirectory.wrap.addressgroup = \|,

- Setup:

\# Don't want the comma at the end of the address groups

plugin.tx\_wtdirectory\_pi1 {

detail {

field.addressgroup = TEXT

field.addressgroup.field = addressgroup

field.addressgroup.split {

token = ,

cObjNum = 1 \|\*\| 2 \|\*\| 3 \|\| 4

1.current = 1

1.wrap = \|,

2.current = 1

2.wrap = \|,

3.current = 1

3.wrap = \|

4.current = 1

}

}

}

