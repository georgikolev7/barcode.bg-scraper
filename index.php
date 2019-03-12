<?php

  require_once 'scraper.class.php';

  $scrape = new Scraper();

  /*
  *  Връща резултат на артикула с най-висок рейтинг
  */

  $result = $scrape->searchBarcode('3800093500671');

  /*
  *   Функцията търси баркод по въведено име на артикул
  */

  $result_2 = $scrape->searchBarcodeName('450 ГР.ХАСКОВСКИ КОЗУНАК ШОКОЛАД');
