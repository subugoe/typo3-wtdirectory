

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


How can I add a new marker and use typoscript
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

- ###WTDIRECTORY\_TS\_TEST### can be used in every HTML Template

- Fill this example via typoscript:

plugin.tx\_wtdirectory\_pi1 {

dynamicTyposcript {

test = TEXT

test.value = typoscript blabla

}

}

