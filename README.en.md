# glpi-roomreservation

🇵🇹 [Versão portuguesa aqui](README.md)

GLPI 11 plugin that adds a new asset type, **Room** (meetings, training, whatever), and
makes it reservable through GLPI's native reservation engine — the same way computers or
projectors already are. I built this to manage meeting room bookings, right after the
vehicles plugin.

It's the sibling of [glpi-vehiclereservation](https://github.com/SantiPT007/glpi-vehiclereservation):
same architecture, only the fields change. And just like there, the point is not
reinventing anything — the calendar, availability, who reserved, dates and times all come
from GLPI. The plugin just creates the asset and registers it as reservable with
`Plugin::registerClass(..., ['reservation_types' => true])`.

## Fields

| Field | Required | Notes |
|-------|:--------:|-------|
| Name | no | free label; if empty, "Building Floor (Room N)" is shown |
| Building | no | e.g. Main building |
| Floor | no | e.g. 2, ground, -1 |
| Capacity | no | number of people |
| Room number | **yes** | unique |
| Location | no | GLPI Locations dropdown; the native reservation list uses this |

## Before installing

- GLPI 11.x, PHP 8.1+, MariaDB 10.11 or equivalent
- the [Reservation Alert](https://github.com/SantiPT007/glpi-reservationalert) plugin must
  be installed and enabled — room reservations are native reservations
  (`glpi_reservations`) and that plugin already handles notifications and the cron for any
  reservation, so I didn't duplicate that code here. Installation is blocked if it's not
  active.

## Installing

```bash
cd /var/www/glpi/plugins
git clone https://github.com/SantiPT007/glpi-roomreservation roomreservation
chown -R www-data:www-data roomreservation
```

The folder really has to be called `roomreservation`, GLPI uses the folder name as the
plugin identifier. Then: Setup → Plugins → Gestão e Reserva de Salas → Install → Enable.
This creates the `glpi_plugin_roomreservation_rooms` table and grants the room management
right to whoever already has config permission.

## Using it

1. Assets → Rooms → Add (the room number is required)
2. open the room, **Reservations** tab, click "Authorize reservations"
3. from then on it shows up in Tools → Reservations, and the SelfService profile can
   reserve it

If SelfService users can't reserve, check that the profile has the native Reservations
right (Administration → Profiles → SelfService → Tools → Reservations).

## Uninstalling

Disable and uninstall through the UI (removes the table, the right, and only the
reservable items of the Room type — other reservations are untouched), then delete the
folder. If the plugin gets stuck in the list, clear the cache:

```bash
rm -rf /var/www/glpi/files/_cache/*
```

## Languages

The code is in English (GLPI convention) and the UI works in English and Portuguese — the
language follows the GLPI session. PT-PT translations live in `locales/` (domain
`roomreservation`). After editing the `.po`:

```bash
msgfmt locales/pt_PT.po -o locales/pt_PT.mo
```

## License

GPL v2+
