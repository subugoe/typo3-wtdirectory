.. include:: Images.txt

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


How to output new fields of tt\_address?
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

- See this HOWTO on `http://www.typo3.net/forum/list/list\_post//82282
  <http://www.typo3.net/forum/list/list_post//82282>`_

- Add some new fields to tt\_address (e.g. via kickstarter)

- New fields (example from rggooglemap) are automaticly shown in the
  backend:

- |img-16| Enable field via TypoScript:

::

   plugin.tx_wtdirectory_pi1 {
           detail {
                   field.tx_rggooglemap_lat = TEXT
                   field.tx_rggooglemap_lat.field = tx_rggooglemap_lat
                   field.tx_rggooglemap_lat.wrap = <span style=”color: red;”>|</span>
           }
   }

- Add a new marker to your HTML Template:

::

   <!-- ###WTDIRECTORY_DETAIL### begin -->
           ###WTDIRECTORY_TX_RGGOOGLEMAP_LNG###
                   <hr />
                   ###WTDIRECTORY_SPECIAL_ALL###
   <!-- ###WTDIRECTORY_DETAIL### end →

- Add a label for the new field:

::

   plugin.tx_wtdirectory_pi1 {
           _LOCAL_LANG.default.wtdirectory_ttaddress_tx_rggooglemap_lat = Latitude:
           _LOCAL_LANG.de.wtdirectory_ttaddress_tx_rggooglemap_lat = Breitengrad:
   }

- Example FE output:

- |img-17| Example TypoScript fom RTE field:

::

   plugin.tx_wtdirectory_pi1 {
           list {
                   field.tx_temp_tempextend = TEXT
                   field.tx_temp_tempextend.field = tx_temp_tempextend1
                   field.tx_temp_tempextend.parseFunc < lib.parseFunc_RTE
           }
   }

