<?PHP

/**
 * Simpla CMS
 *
 * @copyright     2011 Denis Pikusov
 * @link          http://simp.la
 * @author        Denis Pikusov
 *
 * Этот класс использует шаблон index.tpl,
 * который содержит всю страницу кроме центрального блока
 * По get-параметру module мы определяем что сожержится в центральном блоке
 *
 */

require_once( 'View.php' );

class IndexView extends View{
    
    public $modules_dir = 'view/';
    
    private $cookie_inspiration = 60 * 60 * 24 * 30;

    private $module;
    
    /**
     *
     * Отображение
     *
     */
    public function fetch()
    {
        // Unset the session variables after 48 hours
        if( isset( $_SESSION['time'] ) || isset( $_SESSION['user_ip'] ) ){
            if( time() - ( $_SESSION['time'] ?? 0 ) >= 60 * 60 * 48 ){
                unset( $_SESSION['time'], $_SESSION['user_ip'] );
            }
        }
        
        $this->redirectByCookie( 'go_payment',            '/user' );
        $this->redirectByCookie( 'go_credit_rating_paid', '/user/docs?action=credit_rating_paid&payment_id=' . ( $_COOKIE['go_credit_rating_paid'] ?? '' ) );
        
        $set_theme = $this->request->get( 'set_theme' );
        if( $set_theme ){
            
            $set_theme === 'new'   && setcookie( 'theme', 'new', time() + 86400 * 30, '/', $this->config->main_domain, 1 );
            $set_theme === 'unset' && setcookie( 'theme', null,  time() - 1,          '/', $this->config->main_domain, 1 );
            
            header( 'Location:' . $this->request->url( [ 'set_theme' => null ] ) );
        }

        $this->initDirectories();
        
        $this->processVisitor();
        $this->processUTM();
        if( ! $this->processModule() ){
            return false;
        }
        
        // uniq log
        // @todo useless and will be deleted. There is no place for debug in prod
        if( 0 && $this->is_developer ){
            if( empty( $_SESSION['uniq_log'] ) ){
                $log_filename = $this->config->root_dir . 'logs/uniq.log';
                if( file_exists( $log_filename ) && date( 'd', filemtime( $log_filename ) ) != date( 'd' ) ){
                    $archive_filename = $this->config->root_dir . 'logs/archive/' . date( 'y.m.d.', filemtime( $log_filename ) ) . 'uniq.log';
                    rename( $log_filename, $archive_filename );
                    file_put_contents( $log_filename, "\xEF\xBB\xBF" );
                }
                
                $log = PHP_EOL;
                $log .= '*************************************************************************' . PHP_EOL;
                $log .= date( 'd.m.y H:i:s' ) . PHP_EOL;
                $log .= 'IP: ' . ( empty( $_SERVER['REMOTE_ADDR'] ) ? '' : $_SERVER['REMOTE_ADDR'] ) . PHP_EOL;
                $log .= 'REFERER: ' . ( empty( $_SERVER['HTTP_REFERER'] ) ? '' : $_SERVER['HTTP_REFERER'] ) . PHP_EOL;
                $log .= 'URL: ' . $this->request->url() . PHP_EOL;
                $log .= 'AGENT: ' . ( empty( $_SERVER['HTTP_USER_AGENT'] ) ? '' : $_SERVER['HTTP_USER_AGENT'] ) . PHP_EOL;
                $log .= 'IS_ADMIN: ' . ( ! empty( $_SESSION['admin'] ) || ! $this->is_developer ? 'YES' : 'NO' ) . PHP_EOL;
                $log .= 'USER_ID: ' . ( empty( $_COOKIE['user_id'] ) ? '' : $_COOKIE['user_id'] ) . PHP_EOL;
                $log .= '*************************************************************************' . PHP_EOL;
                file_put_contents( $log_filename, $log, FILE_APPEND );
                
                $log_filename = $this->config->root_dir . 'logs/uniq_f.log';
                if( file_exists( $log_filename ) && date( 'd', filemtime( $log_filename ) ) != date( 'd' ) ){
                    $archive_filename = $this->config->root_dir . 'logs/archive/' . date( 'y.m.d.', filemtime( $log_filename ) ) . 'uniq_f.log';
                    rename( $log_filename, $archive_filename );
                    file_put_contents( $log_filename, "\xEF\xBB\xBF" );
                }
                
                $bot    = ( ( stripos( 'bot', $_SERVER['HTTP_USER_AGENT'] ) !== false ) || ( stripos(
                                                                                                 'Bot',
                                                                                                 $_SERVER['HTTP_USER_AGENT']
                                                                                             ) !== false ) );
                $source = ! empty( $_COOKIE['utm_source'] );
                
                if( ! $bot && ! $source ){
                    $log = PHP_EOL;
                    $log .= '*************************************************************************' . PHP_EOL;
                    $log .= date( 'd.m.y H:i:s' ) . PHP_EOL;
                    $log .= 'IP: ' . ( empty( $_SERVER['REMOTE_ADDR'] ) ? '' : $_SERVER['REMOTE_ADDR'] ) . PHP_EOL;
                    $log .= 'REFERER: ' . ( empty( $_SERVER['HTTP_REFERER'] ) ? '' : $_SERVER['HTTP_REFERER'] ) . PHP_EOL;
                    $log .= 'URL: ' . $this->request->url() . PHP_EOL;
                    $log .= 'AGENT: ' . ( empty( $_SERVER['HTTP_USER_AGENT'] ) ? '' : $_SERVER['HTTP_USER_AGENT'] ) . PHP_EOL;
                    $log .= 'IS_ADMIN: ' . ( empty( $_SESSION['admin'] ) ? 'NO' : 'YES' ) . PHP_EOL;
                    $log .= 'USER_ID: ' . ( empty( $_COOKIE['user_id'] ) ? '' : $_COOKIE['user_id'] ) . PHP_EOL;
                    $log .= '*************************************************************************' . PHP_EOL;
                    file_put_contents( $log_filename, $log, FILE_APPEND );
                }
                
                $_SESSION['uniq_log'] = true;
            }
        
        }
        $this->initFlow();
        $this->generateFlowVar();
        
        if( empty( $_SESSION['referer'] ) && isset( $_SERVER['HTTP_REFERER'] ) ){
            $_SESSION['referer'] = $_SERVER['HTTP_REFERER'];
        }
        
        $has_orders = ! empty( $this->user )
            ? (int) $this->orders->hasOrdersByUserId( (int)( $this->user->id ?? 0 ) )
            : 0;
        $this->design->assign( 'has_orders',   $has_orders ); // наличие заявок
        $this->design->assign( 'is_landing',   $this->module === 'MainView' ); // наличие заявок
        $this->design->assign( 'module',       $this->module );     // Передаем название модуля в шаблон, это может пригодиться
        $this->design->assign( 'pages',        $this->pages->get_pages( [ 'visible' => 1 ] ) );
        $this->design->assign( 'docs',         $this->docs->get_docs( [ 'visible' => 1 ] ) );
        $this->design->assign( 'lk_url',       $this->getLKLink() );
        
        ! empty( $_COOKIE['close_inform'] )      && $this->design->assign( 'close_inform', 1 );
        ! empty( $_SESSION['user_id'] )          && $this->design->assign( 'user_login', 1 );
        ! empty( $_COOKIE['consultation_send'] ) && $this->design->assign( 'consultation_send', 1 );
        ! empty( $_SESSION['splash_salut'] )     && $this->design->assign( 'sms_salut', 1 );
        ! empty( $_SESSION['pixel'] )            && $this->design->assign( 'pixel', $_SESSION['pixel'] );
        if( ! empty( $_SESSION['splash_salut'] ) ) unset( $_SESSION['splash_salut'] );
        if( ! empty( $_SESSION['pixel'] ) )        unset( $_SESSION['pixel'] );
        
        $this->design->assign(
            'webmaster_id',
            $this->request->get( 'webmaster_id' )
                ?? ( $this->user ? $this->user->webmaster_id : null )
                   ?? $_COOKIE['webmaster_id']
                      ?? ''
        );
        $this->design->assign( // метка перехода, из GET иначе с пользователя или с куксов
            'utm_source',
            $this->request->get( 'utm_source' )
                ?: ( $this->user ? $this->user->utm_source : null )
                    ?: $_COOKIE['utm_source']
                       ?? ''
        );

        $this->design->assign('automation_fails', $this->getAutomationFails());

        $notOrganicSources = $this->config->non_organic_sources ?? [];
        $urlParts = parse_url($_SERVER['REQUEST_URI']);
        if (!empty($urlParts['query'])) {
            parse_str($urlParts['query'], $query);
        }
        #$isOrganic = empty($query['utm_source']) || !in_array($query['utm_source'], $notOrganicSources);
        $isOrganic = true;
        if (!empty($query['utm_source'])) {
            $isOrganic = !in_array($query['utm_source'], $notOrganicSources);
        } elseif (!empty($_COOKIE['utm_source'])) {
            $isOrganic = !in_array($_COOKIE['utm_source'], $notOrganicSources);
        }
        // Органика?
        $this->design->assign( 'is_organic', $isOrganic);
        
        /** !!!! Не назначайте переменные шаблону после этого блока кода, они будут проигнорированы по неизвестной причине !!!! */
        // Создаем основной блок страницы
        $content = $this->main->fetch();
        if( ! $content ){
            return false;
        }
        $this->design->assign( 'content',      $content );    // Передаем основной блок в шаблон

        // Создаем текущую обертку сайта (обычно index.tpl)
        $wrapper = $this->design->get_var( 'wrapper' );
        if( is_null( $wrapper ) ){
            $wrapper = 'index.tpl';
        }
        
        $this->body = ! empty( $wrapper )
            ? $this->design->fetch( $wrapper )
            : $content;
        
        return $this->body;
    }
    
    private function isBot(): bool
    {
        return (
            ( stripos( $_SERVER['HTTP_USER_AGENT'], 'bot' ) !== false )
            || ( stripos( $_SERVER['HTTP_USER_AGENT'], 'Bot' ) !== false )
            || ( stripos( $_SERVER['HTTP_USER_AGENT'], 'developers.google.com' ) !== false )
            || ( stripos( $_SERVER['HTTP_USER_AGENT'], 'Google Favicon' ) !== false )
        
        );
    }
    
    private function isCRM(): bool
    {
        return isset( $_SERVER['HTTP_REFERER'] ) && stripos( $_SERVER['HTTP_REFERER'], 'manager.boostra.ru' ) !== false;
    }
    
    private function isImage(): bool
    {
        return (
            ( stripos( $_SERVER['REQUEST_URI'], '.png' ) !== false )
            || ( stripos( $_SERVER['REQUEST_URI'], '.jpg' ) !== false )
            || ( stripos( $_SERVER['REQUEST_URI'], '.jpeg' ) !== false )
            || ( stripos( $_SERVER['REQUEST_URI'], '.ico' ) !== false )
        
        );
    }
    
    private function processUTM(): void
    {
        $utm_source = $this->request->get( 'utm_source' );
        $utm_term   = $this->config->main_domain;

        if (empty($_COOKIE['promocode']) && ($promocode = $this->request->get('promocode'))) {
            setcookie('promocode', trim($promocode), time() + (60 * 60 * 24 * 60), '/', $this->config->main_domain);
        }

        if( ! $utm_source ){
            return;
        }
        
        $utm_medium   = (string)$this->request->get( 'utm_medium' );
        $utm_campaign = (string)$this->request->get( 'utm_campaign' );
        $utm_content  = (string)$this->request->get( 'utm_content' );
        $webmaster_id = (string)$this->request->get( 'webmaster_id' );
        $affilate_id  = (string)$this->request->get( 'affilate_id' );
        $click_hash   = $this->request->get( 'hash', 'string' )
            ?: $this->request->get( 'click_hash', 'string' )
                ?: $this->request->get( 'click_id', 'string' );
        
        $utm_medium = $utm_source === 'alliance' ? $this->request->get( 'sub1', 'string' ) : $utm_medium;
        $utm_medium = $utm_medium === 'sms' && strpos( $webmaster_id, 'x' ) != false ? 'x' : $utm_medium;
        
        if(
            $utm_source === 'sms' &&
            in_array( $webmaster_id, [ '0111', '0112', '0113', '0114', '0115', '0116', '0117', '0118', '5555' ] ) &&
            empty( $_SESSION['time'] ) &&
            empty( $_SESSION['user_ip'] )
        ){
            $_SESSION['time']    = time();
            $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['web']     = $webmaster_id;

            $this->users->add_loan_funnel_report( $_SERVER['REMOTE_ADDR'], $webmaster_id );
        }

        if( in_array( $utm_source, [ 'cityads', 'guruleads', 'guruleads_v2' ] ) ){
            $click_hash   = $this->request->get( 'click_id', 'string' );
            $webmaster_id = $this->request->get( 'utm_campaign', 'string' );
        }
        
        if( $utm_source === 'leads.su' ){
            $webmaster_id = $this->request->get( 'affiliate_id', 'string' );
            $click_hash   = $this->request->get( 'transaction_id', 'string' );
            setcookie( "transaction_id", $click_hash, time() + ( 60 * 60 * 24 * 60 ), '/', $this->config->main_domain );
        }

        if( $utm_source === 'leadssu2' ){
            $webmaster_id = $this->request->get( 'affiliate_id', 'string' );
            $click_hash   = $this->request->get( 'transaction_id', 'string' );
            setcookie( "transaction_id", $click_hash, time() + ( 60 * 60 * 24 * 60 ), '/', $this->config->main_domain );
        }

        if ($utm_source === 'finuslugi') {
            $click_hash = $this->request->get('aff_sub1', 'string');
        }

        $prefix_utm = $this->config->prefix_utm;
        $utm_source = $prefix_utm . '_' . $utm_source;

        $referral    = [
            'utm_source'   => $utm_source,
            'utm_medium'   => $utm_medium,
            'utm_campaign' => $utm_campaign,
            'utm_content'  => $utm_content,
            'utm_term'     => $utm_term,
            'webmaster_id' => $webmaster_id ?: $affilate_id,
            'click_hash'   => $click_hash,
            'link'         => $_SERVER['REQUEST_URI']     ?? '',
            'ip'           => $_SERVER['REMOTE_ADDR']     ?? '',
            'user_agent'   => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'referer'      => $_SERVER['HTTP_REFERER']    ?? '',
            'user_id'      => $_COOKIE['user_id']         ?? '',
        ];
        $referral_id = $this->referrals->add_referral( $referral );
        
        $this->setUTMCookie( $utm_source, $utm_medium, $utm_campaign, $utm_content, $utm_term, $webmaster_id, $click_hash, $referral_id );
        
        if( $this->request->get( 'short' ) ){
            $_SESSION['splash_salut'] = 1;
            header( 'Location:' . $this->config->root_url );
            exit;
        }
    }
    
    /**
     * Redirect by given cookie. Unset cookie.
     *
     * @param $cookie
     * @param $redirect_url
     *
     * @return void
     */
    private function redirectByCookie( $cookie, $redirect_url ): void
    {
        if( ! empty( $_COOKIE[ $cookie ] ) ){
            setcookie( $cookie, null, -1 );
            header( 'Location: ' . $redirect_url );
            exit;
        }
    }
    
    private function setUTMCookie( $utm_source, $utm_medium, $utm_campaign, $utm_content, $utm_term, $webmaster_id, $click_hash, $referral_id = null ): void
    {
        setcookie( "utm_source",   $utm_source,   time() + $this->cookie_inspiration, '/', $this->config->main_domain );
        setcookie( "utm_medium",   $utm_medium,   time() + $this->cookie_inspiration, '/', $this->config->main_domain );
        setcookie( "utm_campaign", $utm_campaign, time() + $this->cookie_inspiration, '/', $this->config->main_domain );
        setcookie( "utm_content",  $utm_content,  time() + $this->cookie_inspiration, '/', $this->config->main_domain );
        setcookie( "utm_term",     $utm_term,     time() + $this->cookie_inspiration, '/', $this->config->main_domain );
        setcookie( "webmaster_id", $webmaster_id, time() + $this->cookie_inspiration, '/', $this->config->main_domain );
        setcookie( "click_hash",   $click_hash,   time() + $this->cookie_inspiration, '/', $this->config->main_domain );
        
        if( $referral_id ){
            setcookie( 'referral_id', $referral_id, time() + 86400 * 30, '/', $this->config->main_domain);
        }
    }
    
    private function processVisitor(): void
    {
        if( $this->isNewVisitor() ){
            if( ! $this->isBot() && ! $this->isImage() && ! $this->isCRM() ){
                $utm_source      = (string)$this->request->get( 'utm_source' );
                $webmaster_id    = (string)$this->request->get( 'webmaster_id' );
                $visitor_id      = $this->visitors->add_visitor( [
                    'created'      => date( 'Y-m-d H:i:s' ),
                    'last_active'  => date( 'Y-m-d H:i:s' ),
                    'user_agent'   => $_SERVER['HTTP_USER_AGENT'] ?? '',
                    'referer'      => $_SERVER['HTTP_REFERER'] ?? '',
                    'link'         => $_SERVER['REQUEST_URI'] ?? '',
                    'ip'           => $_SERVER['REMOTE_ADDR'] ?? '',
                    'utm_source'   => $utm_source,
                    'webmaster_id' => $webmaster_id,
                ] );
                $_SESSION['vid'] = $visitor_id;
            }
            
            return;
        }
        
        // Посетитель известен
        // Eсли есть пул метрик обновим их
        $update_data['last_active'] = date( 'Y-m-d H:i:s' );
        $update_data['user_id'] = $_SESSION['user_id'] ?? null;

        if (!empty($_SESSION['user_id']) && !empty($_SESSION['metric_actions'])) {
            $loan_history_json = is_string($this->user->loan_history) ? $this->user->loan_history : json_encode(
                $this->user->loan_history
            );

            $this->custom_metric->updateMetricActionsByIds(
                $_SESSION['metric_actions'],
                [
                    'client_type' => (int)!empty(json_decode($loan_history_json)),
                    'user_id' => (int)$_SESSION['user_id'],
                ]
            );

            unset($_SESSION['metric_actions']);
        }

        $this->visitors->update_visitor($_SESSION['vid'], $update_data);
    }
    
    private function isNewVisitor(): bool
    {
        return empty( $_SESSION['vid'] );
    }
    
    private function getLKLink(): string
    {
        switch( true ){
            case isset( $_SESSION['passport_user'] ):                        return 'user/passport';
            case isset( $_SESSION[ $this->account_contract::SESSION_KEY ] ): return 'user/contract';
            default:                                                         return 'user/login';
        }
    }

    private function getAutomationFails()
    {
        $this->db->query("SELECT * FROM automation_fails WHERE type != 'soap_error'");
        return $this->db->results();
    }
    
    private function processModule(): bool
    {
        // Текущий модуль (для отображения центрального блока)
        $module = preg_replace( "/[^A-Za-z0-9]+/", "", $this->request->get( 'module', 'string' ) );
        
        // Если не задан - берем из настроек
        if( empty( $module ) ){
            return false;
        }
        
        // todo в дальнейшем при полном редизайне заменить для всех страниц, в БД настройка 'theme' и удалить табличку s_theme_view
        if (in_array($module, ['CarDepositView', 'InitUserView', 'CompanyFormView'])) {
            $theme = 'orange_theme';
            $this->design->smarty->compile_dir = $this->config->root_dir . '/compiled/' . $theme;
            $this->design->smarty->template_dir = $this->config->root_dir . '/design/' . $theme . '/html';
        }
        
        // Создаем соответствующий класс
        if( ! is_file( $this->modules_dir . "$module.php" ) ){
            return false;
        }
        
        include_once( $this->modules_dir . "$module.php" );
        
        if( ! class_exists( $module ) ){
            return false;
        }
        
        $this->module = $module;
        $this->main   = new $module( $this );
        
        return true;
    }

    /**
     * @return void
     */
    private function initDirectories()
    {
        $dirs = [
            $this->config->root_dir . '/files',
            $this->config->root_dir . '/files/contracts',
        ];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir);
            }
        }
    }
}
