SELECT abo.ppua, abo.`start-date` , abo.`stop-date`, abo.access, providers.providername,providers.chid, cards.ppsa
FROM neovision.providers join neovision.abo join neovision.cards
on providers.chid = abo.chid and cards.ppua = abo.ppua and cards.deleted = 0;
/*
U:80000000 S:01/01/2020 E:31/12/2020 A:7fffffff N:MasterCard SA:999999 ID:1010
U:80000001 S:01/11/2020 E:30/11/2020 A:1ff N:NeoVisions SA:990000 ID:1010
U:80000002 S:01/10/2020 E:31/10/2020 A:3 N:NeoVisions SA:990000 ID:1010
U:80000018 S:01/10/2020 E:31/10/2020 A:7 N:NeoVisions SA:990000 ID:1010

*/