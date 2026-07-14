<?php

// lista/pesquisa de salas (pesquisa nativa do GLPI)

include('../../../inc/includes.php');

\Session::checkRight('plugin_roomreservation_room', READ);

\Html::header(
    \GlpiPlugin\Roomreservation\Room::getTypeName(\Session::getPluralNumber()),
    $_SERVER['PHP_SELF'],
    'assets',
    \GlpiPlugin\Roomreservation\Room::class
);

\Search::show(\GlpiPlugin\Roomreservation\Room::class);

\Html::footer();
