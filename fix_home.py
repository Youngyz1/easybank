with open('C:/xampp/htdocs/easybank/home.php', 'r') as f:
    content = f.read()

old = """     if (class_exists('DATABASE_CONNECT'))
            {
 
             $obj_conn  = new DATABASE_CONNECT;
            
             $conn = $obj_conn->get_connection();
                        }


                else
                  {"""

new = """     if (class_exists('DATABASE_CONNECT'))
            {
 
             $obj_conn  = new DATABASE_CONNECT;
            
             $conn = $obj_conn->get_connection();
             {"""

if old in content:
    content = content.replace(old, new)
    with open('C:/xampp/htdocs/easybank/home.php', 'w') as f:
        f.write(content)
    print('Fixed successfully!')
else:
    print('Pattern not found - checking whitespace...')
    # Try to find the area
    idx = content.find('class_exists')
    print(repr(content[idx:idx+200]))