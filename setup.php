<?php

// unico ficheiro carregado pelo GLPI 11 para descoberta e init do plugin

if (!defined('PLUGIN_ROOMRESERVATION_VERSION')) {
    define('PLUGIN_ROOMRESERVATION_VERSION', '1.0.0');
}
if (!defined('PLUGIN_ROOMRESERVATION_MIN_GLPI')) {
    define('PLUGIN_ROOMRESERVATION_MIN_GLPI', '11.0.0');
}
if (!defined('PLUGIN_ROOMRESERVATION_MAX_GLPI')) {
    define('PLUGIN_ROOMRESERVATION_MAX_GLPI', '11.0.99');
}

function plugin_version_roomreservation(): array
{
    return [
        'name'         => 'Gestão e Reserva de Salas',
        'version'      => PLUGIN_ROOMRESERVATION_VERSION,
        'author'       => 'Santiago Almendra',
        'license'      => 'GPL v2+',
        'homepage'     => 'https://github.com/SantiPT007/glpi-roomreservation',
        'requirements' => [
            'glpi' => [
                'min' => PLUGIN_ROOMRESERVATION_MIN_GLPI,
                'max' => PLUGIN_ROOMRESERVATION_MAX_GLPI,
            ],
        ],
    ];
}

function plugin_roomreservation_check_prerequisites(): bool
{
    if (version_compare(GLPI_VERSION, PLUGIN_ROOMRESERVATION_MIN_GLPI, 'lt')) {
        echo sprintf(__('This plugin requires GLPI >= %s', 'roomreservation'), PLUGIN_ROOMRESERVATION_MIN_GLPI);
        return false;
    }

    // dependencia obrigatoria: as notificacoes/cron das reservas de salas sao fornecidas
    // pelo Reservation Alert (as reservas de sala sao reservas nativas, ver pelo cron dele).
    if (!(new Plugin())->isActivated('reservationalert')) {
        echo __('Requires the "Reservation Alert" (reservationalert) plugin installed and enabled — it provides the tray notifications and the reservations cron.', 'roomreservation');
        return false;
    }

    return true;
}

function plugin_roomreservation_check_config(bool $verbose = false): bool
{
    return true;
}

// init, corre em cada pedido autenticado
function plugin_init_roomreservation(): void
{
    /** @var array $PLUGIN_HOOKS */
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['roomreservation'] = true;

    $plugin = new Plugin();
    if (!$plugin->isActivated('roomreservation')) {
        return;
    }

    // torna a sala um asset reservavel pelo motor NATIVO de reservas do GLPI:
    // acrescenta o itemtype a $CFG_GLPI['reservation_types'] (ver Plugin::registerClass)
    Plugin::registerClass('GlpiPlugin\\Roomreservation\\Room', [
        'reservation_types' => true,
    ]);

    // entrada de menu propria, na seccao Ativos, com icone de porta/sala
    $PLUGIN_HOOKS['menu_toadd']['roomreservation'] = [
        'assets' => 'GlpiPlugin\\Roomreservation\\Room',
    ];

    // sem pagina de configuracao propria: notificacoes/cron vem do Reservation Alert e as
    // reservas usam o motor nativo.
}

function plugin_roomreservation_install(): bool
{
    return plugin_roomreservation_do_install();
}

function plugin_roomreservation_uninstall(): bool
{
    return plugin_roomreservation_do_uninstall();
}
