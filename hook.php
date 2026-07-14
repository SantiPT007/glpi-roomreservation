<?php

use GlpiPlugin\Roomreservation\Room;

// instalacao: cria a tabela do asset e o direito de gestao para os admins
function plugin_roomreservation_do_install(): bool
{
    global $DB;

    $schema = Plugin::getPhpDir('roomreservation') . '/sql/install.sql';
    if (file_exists($schema)) {
        $DB->runFile($schema);
    }

    $migration = new Migration(PLUGIN_ROOMRESERVATION_VERSION);

    // direito proprio do asset, concedido a quem ja tem config UPDATE (super-admin etc.)
    $migration->addRight('plugin_roomreservation_room', ALLSTANDARDRIGHT, ['config' => UPDATE]);
    $migration->executeMigration();

    return true;
}

// desinstalacao limpa: remove a tabela, o direito e os itens reservaveis orfaos do nosso tipo
function plugin_roomreservation_do_uninstall(): bool
{
    global $DB;

    // limpa as reservas e os itens reservaveis criados para o nosso itemtype (nao mexe no resto do core)
    $itemtype = Room::class;
    $reservationitems_ids = [];
    foreach ($DB->request([
        'SELECT' => 'id',
        'FROM'   => 'glpi_reservationitems',
        'WHERE'  => ['itemtype' => $itemtype],
    ]) as $row) {
        $reservationitems_ids[] = (int) $row['id'];
    }
    if (!empty($reservationitems_ids)) {
        $DB->delete('glpi_reservations', ['reservationitems_id' => $reservationitems_ids]);
        $DB->delete('glpi_reservationitems', ['itemtype' => $itemtype]);
    }

    if ($DB->tableExists('glpi_plugin_roomreservation_rooms')) {
        $DB->dropTable('glpi_plugin_roomreservation_rooms');
    }

    ProfileRight::deleteProfileRights(['plugin_roomreservation_room']);

    return true;
}
