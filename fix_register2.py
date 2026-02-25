with open('C:/xampp/htdocs/easybank/page-register2.php', 'r') as f:
    content = f.read()

fixes = {
    "$day              =   $obj_secure_data->SECURE_DATA_ENTER($_POST['day']);": 
        "$day              =   preg_replace('/[^0-9]/', '', $_POST['day']);",
    
    "$month            =   $obj_secure_data->SECURE_DATA_ENTER($_POST['month']);": 
        "$month            =   preg_replace('/[^0-9]/', '', $_POST['month']);",
    
    "$year             =   $obj_secure_data->SECURE_DATA_ENTER($_POST['year']);": 
        "$year             =   preg_replace('/[^0-9]/', '', $_POST['year']);",
    
    "$number           =   $obj_secure_data->SECURE_DATA_ENTER($_POST['number']);": 
        "$number           =   preg_replace('/[^0-9]/', '', $_POST['number']);",
    
    "$post_code        =   $obj_secure_data->SECURE_DATA_ENTER($_POST['post_code']);": 
        "$post_code        =   preg_replace('/[^0-9]/', '', $_POST['post_code']);",
    
    "$tax_id_number    =   $obj_secure_data->SECURE_DATA_ENTER($_POST['tax_id_number']);": 
        "$tax_id_number    =   preg_replace('/[^0-9]/', '', $_POST['tax_id_number']);",
}

fixed = 0
for old, new in fixes.items():
    if old in content:
        content = content.replace(old, new)
        print(f'Fixed: {old.strip()[:50]}...')
        fixed += 1
    else:
        print(f'Not found: {old.strip()[:50]}...')

if fixed > 0:
    with open('C:/xampp/htdocs/easybank/page-register2.php', 'w') as f:
        f.write(content)
    print(f'\nTotal fixed: {fixed} fields')
else:
    print('Nothing fixed - check the file manually')