<?php
/*Template Name: Boxmoe注册页*/
	
//如果用户已经登陆那么跳转到首页
if (is_user_logged_in()){
  wp_safe_redirect( get_option('home') );
  exit;
}
	
//获取注册页面提交时候的表单数据

if( !empty($_POST['csyor_reg']) ) {
  $error = '';
  $redirect_to = sanitize_user( $_REQUEST['redirect_to'] );
  $sanitized_user_login = sanitize_user( $_POST['user_login'] );
  $user_website = sanitize_user( $_POST['website'] );
  $user_nickname = sanitize_user( $_POST['nickname'] );
  $user_email = apply_filters( 'user_registration_email', $_POST['user_email'] );
  $comment_aaa      	  = ( isset($_POST['aaa']) ) ? trim($_POST['aaa']) : '0';
  $comment_bbb          = ( isset($_POST['bbb']) ) ? trim($_POST['bbb']) : '0';
  $comment_subab        = ( isset($_POST['subab']) ) ? trim($_POST['subab']) : '0';
	
  // 验证邮箱
  if ( $user_email == '' ) {
    $error .= '错误：请填写电子邮件地址。';
  } elseif ( ! is_email( $user_email ) ) {
    $error .= '错误：电子邮件地址不正确。';
    $user_email = '';
  } elseif ( email_exists( $user_email ) ) {
    $error .= '错误：该电子邮件地址已经被注册，请换一个。';
  }
	
  // 验证用户名
  elseif ( $sanitized_user_login == '' ) {
    $error .= '错误：请输入登陆账号。';
  } elseif ( !preg_match("/^[a-zA-Z0-9_]{4,16}$/",$sanitized_user_login) ) {
    $error .= '错误：登陆账号只能包含字母、数字、下划线，长度4到16位。';
    $sanitized_user_login = '';
  } elseif ( username_exists( $sanitized_user_login ) ) {
    $error .= '错误：该用户名已被注册，请再选择一个。';
  }
	
  //验证密码
  elseif(strlen($_POST['user_pass']) < 6){
    $error .= '错误：密码长度至少6位。';
  }elseif($_POST['user_pass'] != $_POST['user_pass2']){
    $error .= '错误：两次输入的密码必须一致。';
  }elseif(((int)$comment_subab)!=(((int)$comment_aaa)+((int)$comment_bbb))){
    $error .= '错误：请输入正确的计算结果验证码。';	
  }
	
  if($error == '') {
    //验证全部通过进入注册信息添加
    $display_name = empty($user_nickname)?$sanitized_user_login:$user_nickname;
    $user_pass = $_POST['user_pass'];
    $user_id = wp_insert_user( array ( 
      'user_login' => $sanitized_user_login, 
      'user_pass' => $user_pass , 
      'nickname' => $user_nickname,
      'display_name' => $display_name, 
      'user_email' => $user_email, 
      'user_url' => $user_website) ) ;
		
    //意外情况判断，添加失败
    if ( ! $user_id ) {
      $error .= sprintf( '错误：无法完成您的注册请求... 请联系<a href=\"mailto:%s\">管理员</a>！</p>', get_option( 'admin_email' ) );
    }else if (!is_user_logged_in()) {
      //注册成功发送邮件通知用户
      $to = $user_email;
      $subject = '您在 [' . get_option("blogname") . '] 的注册已经成功';
      $message = '<div style="background-color:#eef2fa; border:1px solid #d8e3e8; color:#111; padding:0 15px; -moz-border-radius:5px; -webkit-border-radius:5px; -khtml-border-radius:5px; border-radius:5px;">
        <p>' . $user_nickname . ', 您好!</p>
        <p>感谢您在 [' . get_option("blogname") . '] 注册用户~</p>
        <p>你的注册信息如下:<br />
        账号：'. $sanitized_user_login . '<br />
        邮箱：'. $user_email . '<br />
        密码：'. $_POST['user_pass'] . '<br />
        </p>
        <p>欢迎光临 <a href="'.get_option('home').'">' . get_option('blogname') . '</a>。</p>
	<p>(此郵件由系統自動發出, 請勿回覆.)</p>
	</div>';
      $from = "From: \"" . get_option('blogname') . "\" <$wp_email>";
      $headers = "$from\nContent-Type: text/html; charset=" . get_option('blog_charset') . "\n";
      wp_mail( $to, $subject, $message, $headers );
			
      $user = get_userdatabylogin($sanitized_user_login);
      $user_id = $user->ID;
			
      // 自动登录
      wp_set_current_user($user_id, $user_login);
      wp_set_auth_cookie($user_id);
      do_action('wp_login', $user_login);
			
      wp_safe_redirect( $redirect_to );
    }
  }
}	
?>
<?php
if (!is_user_logged_in()) { get_header(); ?>
<link rel='stylesheet'  href='<?php echo boxmoe_load_style();?>/assets/css/login.css' type='text/css' media='all' />
      <div class="container">
        <div class="section-head">
          <span>Registered</span></div>
      </div>
      <div class="boxmoe-blog-content mb30">
        <div class="container form-body">
          <div class="row">
            <div class="col-md-10 col-lg-10 ml-auto mr-auto">	  
            <div class="form-holder">			
                <div class="form-content" style="background-image: url('<?php echo boxmoe_load_style();?>/assets/images/login.jpg');">
                    <div class="form-items">
                        <div class="page-links">
                            <a href="<?php get_login_url(); ?>?r=<?php get_user_url(); ?>">登录会员</a><a href="<?php get_reg_url(); ?>" class="active">注册会员</a>
                        </div>
                        <form name="registerform" method="post" action="#" >
						    <input class="form-control input-group-alternative" placeholder="用户名（*必填）" name="user_login" id="user_login" tabIndex="2" value="<?php if(!empty($sanitized_user_login)) echo $sanitized_user_login; ?>" required type="text">
							<input class="form-control input-group-alternative" placeholder="输入密码（*必填）" tabindex="3" id="user_pwd1" name="user_pass"  required type="password">
							<input class="form-control input-group-alternative" placeholder="重复输入密码（*必填）" tabindex="4" id="user_pwd2" name="user_pass2"  required type="password">
							<input class="form-control input-group-alternative" placeholder="Email（*必填）" name="user_email" id="user_email" tabIndex="1" value="<?php if(!empty($user_email)) echo $user_email; ?>" required type="email">
							<input class="form-control input-group-alternative" placeholder="昵称" tabindex="5" id="nickname" name="nickname" type="text">
							<input class="form-control input-group-alternative" placeholder="网址" tabindex="6" id="website" name="website" type="text">
							<div class="form-group">
				<label class="form-control-label  input-group-alternative" >验证问题：<?php $aaa=rand(1,9); $bbb=rand(1,9); ?>
							<?php echo $aaa; ?>+<?php echo $bbb; ?>=
							<input name="aaa" value="<?php echo $aaa; ?>" type="hidden" />
							<input name="bbb" value="<?php echo $bbb; ?>" type="hidden" />
							</label>
                  <div class="input-group input-group-merge input-group-alternative mb-3">
                    <div class="input-group-prepend">
                      <span class="input-group-text"><i class="fa fa-key"></i></span>
                    </div>
                    <input class="form-control" placeholder=" 验证码（*必填）"  name="subab" id="subab" tabindex="8" required type="text">
                  </div>
                </div>

                            <div class="form-button">
							<input type="hidden" name="csyor_reg" value="ok" />
				            <input type="hidden" name="redirect_to" value="<?php get_user_url() ?>"/>
                            <button iname="wp-submit"  id="wp-submit"  tabindex="7" type="submit" class="ibtn">提交注册</button>
                            </div>
                        </form>
                        <div class="other-links">
                            <span>如果忘记密码你有两种选择(╥﹏╥)</span><a href="<?php echo site_url('/') ?>wp-login.php?action=lostpassword">1.找回密码</a><a href="<?php echo get_boxmoe('users_reg') ?>">2.重新注册</a>
                        </div>
                    </div>
                </div>
<?php if(!empty($error)) {
 echo '<div class="text-muted text-center mb20"><div class="alert alert-danger mt20">'.$error.'</div></div>';
}?>						
            </div>
	
            </div>
          </div>
        </div>
      </div>	
<?php } get_footer(); ?>