<?php defined('SYSPATH') or die('No direct script access.');
    $user = Leafx2::get_user_data();
?>
<ul class="nav navbar-nav navbar-right">
    <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo "$user->name ($user->username)"; ?><span class="caret"></span></a>
        <ul class="dropdown-menu" role="menu">
            <li><a href="{APP_BASE}User/chpasswd">Change Password</a></li>
            <li><a href="{APP_BASE}User/profile/?username=<?php echo base64_encode($user->username); ?>">Profile</a></li>
            <li class="divider"></li>
            <li><a href="{APP_BASE}Auth/logout">Logout</a></li>
        </ul>
    </li>
</ul>