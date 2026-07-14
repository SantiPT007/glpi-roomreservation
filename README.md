# glpi-roomreservation

🇬🇧 [English version here](README.en.md)

Plugin para GLPI 11 que acrescenta um tipo de ativo novo, **Sala** (reuniões, formação,
o que for), e o torna reservável pelo motor de reservas nativo do GLPI — igual ao que já
acontece com computadores ou datashows. Fiz isto para gerir a reserva de salas de
reuniões, a seguir ao plugin dos veículos.

É irmão do [glpi-vehiclereservation](https://github.com/SantiPT007/glpi-vehiclereservation):
a arquitetura é a mesma, só mudam os campos. E tal como lá, a ideia é não reinventar nada —
o calendário, a disponibilidade, quem reservou, datas e horas vêm todos do GLPI. O plugin
só cria o asset e regista-o como reservável com
`Plugin::registerClass(..., ['reservation_types' => true])`.

## Campos

| Campo | Obrigatório | Notas |
|-------|:-----------:|-------|
| Nome | não | etiqueta livre; se vazio mostra-se «Edifício Piso (Sala N)» |
| Edifício | não | ex.: Edifício Sede |
| Piso | não | ex.: 2, R/C, -1 |
| Lotação | não | número de pessoas |
| Número da sala | **sim** | único |
| Localização | não | dropdown de Localizações do GLPI; a lista de reservas nativa usa isto |

## Antes de instalar

- GLPI 11.x, PHP 8.1+, MariaDB 10.11 ou equivalente
- o plugin [Reservation Alert](https://github.com/SantiPT007/glpi-reservationalert) tem de
  estar instalado e ativo — as reservas de salas são reservas nativas
  (`glpi_reservations`) e é ele que trata das notificações e do cron para qualquer reserva,
  por isso não repeti esse código aqui. A instalação bloqueia se ele não estiver ativo.

## Instalar

```bash
cd /var/www/glpi/plugins
git clone https://github.com/SantiPT007/glpi-roomreservation roomreservation
chown -R www-data:www-data roomreservation
```

A pasta tem de se chamar mesmo `roomreservation`, o GLPI usa o nome da pasta como
identificador. Depois: Configurar → Plugins → Gestão e Reserva de Salas → Instalar →
Ativar. Isto cria a tabela `glpi_plugin_roomreservation_rooms` e dá o direito de gestão
de salas a quem já tem permissão de configuração.

## Usar

1. Ativos → Salas → Adicionar (o número da sala é obrigatório)
2. abrir a sala, separador **Reservas**, clicar em «Autorizar as reservas»
3. a partir daí aparece em Ferramentas → Reservas, e o perfil SelfService consegue reservar

Se os utilizadores SelfService não conseguirem reservar, confirmar que o perfil tem o
direito nativo de Reservas (Administração → Perfis → SelfService → Ferramentas → Reservas).

## Desinstalar

Desativar e desinstalar pela interface (remove a tabela, o direito e só os itens
reserváveis do tipo Sala — não mexe noutras reservas), depois apagar a pasta. Se o plugin
ficar preso na lista, limpar a cache:

```bash
rm -rf /var/www/glpi/files/_cache/*
```

## Idiomas

O código está em inglês (convenção GLPI) e a interface funciona em inglês e português —
a língua vem da sessão do GLPI. As traduções PT-PT estão em `locales/` (domínio
`roomreservation`). Depois de editar o `.po`:

```bash
msgfmt locales/pt_PT.po -o locales/pt_PT.mo
```

## Licença

GPL v2+
