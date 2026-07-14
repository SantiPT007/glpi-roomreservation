<?php

namespace GlpiPlugin\Roomreservation;

use CommonDBTM;
use Entity;
use Html;
use Location;
use Log;
use Notepad;
use Reservation;
use Session;

// novo tipo de asset "Sala" (reunioes, formacao, etc.), reservavel pelo motor nativo de reservas do GLPI 11.
// os campos quem-reservou/data/hora NAO vivem aqui: vem do sistema nativo de reservas.
class Room extends CommonDBTM
{
    // direito proprio do asset (gestao). a reserva em si usa o direito nativo 'reservation'
    public static $rightname = 'plugin_roomreservation_room';

    // ativa o separador de Histórico
    public $dohistory = true;

    public static function getTypeName($nb = 0)
    {
        return _n('Room', 'Rooms', $nb, 'roomreservation');
    }

    public static function getIcon()
    {
        return 'ti ti-door';
    }

    // nome legivel (lista de reservas nativa, notificacoes, etc.):
    // 1) o campo "name" se preenchido; 2) "Edificio Piso (Sala N)"; 3) "Sala N"; 4) edificio/piso.
    // getFriendlyName() e final no GLPI 11 -> sobrepoe-se computeFriendlyName()
    public function computeFriendlyName()
    {
        $name = trim((string) ($this->fields['name'] ?? ''));
        if ($name !== '') {
            return $name;
        }

        $label  = trim(($this->fields['building'] ?? '') . ' ' . ($this->fields['floor'] ?? ''));
        $number = trim((string) ($this->fields['room_number'] ?? ''));

        if ($label !== '' && $number !== '') {
            return $label . ' (' . sprintf(__('Room %s', 'roomreservation'), $number) . ')';
        }
        if ($number !== '') {
            return sprintf(__('Room %s', 'roomreservation'), $number);
        }
        if ($label !== '') {
            return $label;
        }
        return parent::computeFriendlyName();
    }

    public static function getMenuContent()
    {
        if (!self::canView()) {
            return false;
        }

        $menu = [
            'title' => self::getTypeName(Session::getPluralNumber()),
            'page'  => self::getSearchURL(false),
            'icon'  => self::getIcon(),
            'links' => [
                'search' => self::getSearchURL(false),
            ],
        ];

        if (self::canCreate()) {
            $menu['links']['add'] = self::getFormURL(false);
        }

        return $menu;
    }

    public function defineTabs($options = [])
    {
        $tabs = [];
        $this->addDefaultFormTab($tabs);
        // separador de Reservas nativo: mostra o controlo "permitir reservas" e a lista de reservas
        $this->addStandardTab(Reservation::class, $tabs, $options);
        $this->addStandardTab(Log::class, $tabs, $options);
        $this->addStandardTab(Notepad::class, $tabs, $options);
        return $tabs;
    }

    public function prepareInputForAdd($input)
    {
        if (empty(trim((string) ($input['room_number'] ?? '')))) {
            Session::addMessageAfterRedirect(
                __('Room number is required.', 'roomreservation'),
                false,
                ERROR
            );
            return false;
        }
        return $input;
    }

    public function prepareInputForUpdate($input)
    {
        if (array_key_exists('room_number', $input) && empty(trim((string) $input['room_number']))) {
            Session::addMessageAfterRedirect(
                __('Room number is required.', 'roomreservation'),
                false,
                ERROR
            );
            return false;
        }
        return $input;
    }

    public function showForm($id, array $options = [])
    {
        $this->initForm($id, $options);
        $this->showFormHeader($options);

        echo "<tr class='tab_bg_1'>";
        echo '<td>' . __('Name', 'roomreservation') . '</td>';
        echo '<td>' . Html::input('name', ['value' => $this->fields['name'] ?? '']) . '</td>';
        echo '<td>' . __('Room number', 'roomreservation') . " <span class='red'>*</span></td>";
        echo '<td>' . Html::input('room_number', [
            'value'    => $this->fields['room_number'] ?? '',
            'required' => 'required',
        ]) . '</td>';
        echo '</tr>';

        echo "<tr class='tab_bg_1'>";
        echo '<td>' . __('Building', 'roomreservation') . '</td>';
        echo '<td>' . Html::input('building', ['value' => $this->fields['building'] ?? '']) . '</td>';
        echo '<td>' . __('Floor', 'roomreservation') . '</td>';
        echo '<td>' . Html::input('floor', ['value' => $this->fields['floor'] ?? '']) . '</td>';
        echo '</tr>';

        echo "<tr class='tab_bg_1'>";
        echo '<td>' . __('Capacity (people)', 'roomreservation') . '</td>';
        echo '<td>' . Html::input('capacity', [
            'value' => $this->fields['capacity'] ?? '',
            'type'  => 'number',
            'min'   => '1',
            'max'   => '10000',
        ]) . '</td>';
        echo '<td></td><td></td>';
        echo '</tr>';

        echo "<tr class='tab_bg_1'>";
        echo '<td>' . Entity::getTypeName(1) . '</td>';
        echo '<td>';
        Entity::dropdown(['value' => $this->fields['entities_id'] ?? ($_SESSION['glpiactive_entity'] ?? 0)]);
        echo '</td>';
        echo '<td>' . Location::getTypeName(1) . '</td>';
        echo '<td>';
        Location::dropdown(['value' => $this->fields['locations_id'] ?? 0]);
        echo '</td>';
        echo '</tr>';

        echo "<tr class='tab_bg_1'>";
        echo '<td>' . __('Comment', 'roomreservation') . '</td>';
        echo "<td colspan='3'><textarea class='form-control' name='comment' rows='3'>"
            . htmlspecialchars((string) ($this->fields['comment'] ?? ''), ENT_QUOTES)
            . '</textarea></td>';
        echo '</tr>';

        $this->showFormButtons($options);
        return true;
    }

    public function rawSearchOptions()
    {
        $table = self::getTable();

        $opts = [];

        $opts[] = ['id' => 'common', 'name' => self::getTypeName(Session::getPluralNumber())];

        $opts[] = [
            'id'            => '1',
            'table'         => $table,
            'field'         => 'name',
            'name'          => __('Name', 'roomreservation'),
            'datatype'      => 'itemlink',
            'massiveaction' => false,
        ];

        $opts[] = [
            'id'            => '2',
            'table'         => $table,
            'field'         => 'id',
            'name'          => __('ID'),
            'datatype'      => 'number',
            'massiveaction' => false,
        ];

        $opts[] = [
            'id'       => '3',
            'table'    => $table,
            'field'    => 'room_number',
            'name'     => __('Room number', 'roomreservation'),
            'datatype' => 'string',
        ];

        $opts[] = [
            'id'       => '4',
            'table'    => $table,
            'field'    => 'building',
            'name'     => __('Building', 'roomreservation'),
            'datatype' => 'string',
        ];

        $opts[] = [
            'id'       => '5',
            'table'    => $table,
            'field'    => 'floor',
            'name'     => __('Floor', 'roomreservation'),
            'datatype' => 'string',
        ];

        $opts[] = [
            'id'       => '6',
            'table'    => $table,
            'field'    => 'capacity',
            'name'     => __('Capacity (people)', 'roomreservation'),
            'datatype' => 'number',
        ];

        $opts[] = [
            'id'       => '8',
            'table'    => $table,
            'field'    => 'comment',
            'name'     => __('Comment', 'roomreservation'),
            'datatype' => 'text',
        ];

        $opts[] = [
            'id'       => '9',
            'table'    => 'glpi_locations',
            'field'    => 'completename',
            'name'     => Location::getTypeName(1),
            'datatype' => 'dropdown',
        ];

        $opts[] = [
            'id'            => '19',
            'table'         => $table,
            'field'         => 'date_mod',
            'name'          => __('Last update'),
            'datatype'      => 'datetime',
            'massiveaction' => false,
        ];

        $opts[] = [
            'id'       => '80',
            'table'    => 'glpi_entities',
            'field'    => 'completename',
            'name'     => Entity::getTypeName(1),
            'datatype' => 'dropdown',
        ];

        $opts[] = [
            'id'            => '121',
            'table'         => $table,
            'field'         => 'date_creation',
            'name'          => __('Creation date'),
            'datatype'      => 'datetime',
            'massiveaction' => false,
        ];

        return $opts;
    }
}
