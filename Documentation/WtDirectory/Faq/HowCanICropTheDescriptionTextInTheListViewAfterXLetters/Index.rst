

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


How can I crop the description text in the list view after X letters?
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

- Example typoscript to crop the description in the list view after 200
  letter:

plugin.tx\_wtdirectory\_pi1 {

list {

field.description = TEXT

field.description.field = description

field.description.crop = 200 \| ... \| 1

}

}

