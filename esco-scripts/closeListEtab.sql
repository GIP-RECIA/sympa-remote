select name_list, robot_list  from list_table where status_list in ('closed', 'family_closed')  and name_list not like '%.%.%' and robot_list = '0450786k.list.netocentre.fr';
 
