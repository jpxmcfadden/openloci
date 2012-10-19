<?php //See: http://xataface.com/forum/viewtopic.php?t=4065#20416
class tables_companies {

   var $valuelists = array();

   function init(&$table){
      $app =& Dataface_Application::getInstance();
      
      $sql = array();
      $sql[] = "create temporary table MyCompanyPermissions (
         company_id int(11) not null,
         role varchar(32) not null,
         approval_level int(5) default 0,
         Primary Key (company_id))";
         
         
      if ( isBrokerCustomer() ){
         /**
          * Broker customers can only see companies that have been approved
          * by their dealer.
          */
         $dealer_id = getDealerID();
         
         // First let's add all companies that don't require approval
         $sql[] = "replace into MyCompanyPermissions (company_id,role,approval_level)
            select c.company_id, 'SUBSCRIBER' as role, ct.approval_level from companies c inner join company_types ct on c.resource_type=ct.id where ct.approval_level > '".APPROVAL_LEVEL_NOT_APPROVED."'";
         
         // Next let's remove companies that have had their approval explicitly removed
         $sql[] = "delete from MyCompanyPermissions where company_id in (
            select c.company_id from companies c inner join dealer_companies dc on (c.company_id=dc.company_id and dc.dealer_id='".addslashes($dealer_id)."') where dc.approval_level = '".APPROVAL_LEVEL_NOT_APPROVED."'
            )";
         
         // Next let's add companies that have been explicitly approved
         $sql[] = "replace into MyCompanyPermissions (company_id,role,approval_level) 
            select c.company_id, 'SUBSCRIBER' as role, dc.approval_level from companies c inner join dealer_companies dc on (c.company_id=dc.company_id and dc.dealer_id='".addslashes($dealer_id)."') where dc.approval_level = '".APPROVAL_LEVEL_APPROVED."'";
         
         // Remove all companies that are not resources.
         $sql[] = "delete from MyCompanyPermissions where company_id in (
            select c.company_id from companies c inner join packages p on c.package_id=p.package_id where p.is_resource = 0
            )";
         
         $sql[] = "replace into MyCompanyPermissions (company_id,role) values ('$dealer_id','SUBSCRIBER')";
         
      
      } else if ( isBrokerDealer() ){
         /**
          * Broker dealers should be able to see all companies in the system.
          */
          
         $dealer_id = getDealerID();
         
         
         // Let's add all of the companies with their category's approval level
         $sql[] = "replace into MyCompanyPermissions (company_id,role,approval_level) 
            select c.company_id, 'BROKER' as role, ct.approval_level from companies c inner join company_types ct on c.resource_type=ct.id";
            
         // Next let's add companies that have been explicitly approved or rejected.
         $sql[] = "replace into MyCompanyPermissions (company_id,role,approval_level)
            select c.company_id, 'BROKER' as role, dc.approval_level from companies c inner join dealer_companies dc on (c.company_id=dc.company_id and dc.dealer_id='".addslashes($dealer_id)."')";
            
         // Remove all companies that are not resources.
         $sql[] = "delete from MyCompanyPermissions where company_id in (
            select c.company_id from companies c inner join packages p on c.package_id=p.package_id where p.is_resource = 0
            )";
         
         $sql[] = "replace into MyCompanyPermissions (company_id,role) values ('$dealer_id','OWNER')";
         
      } else if ( isCompanyOwner() ){
         /**
          * Company owners should be able to see all companies.
          */
         
         $company_id = getCompanyID();
         
         // Let's add all companies
         $sql[] = "replace into MyCompanyPermissions (company_id,role)
            select company_id, 'SUBSCRIBER' as role from companies";
         
         // Remove all companies that are not resources.
         $sql[] = "delete from MyCompanyPermissions where company_id in (
            select c.company_id from companies c inner join packages p on c.package_id=p.package_id where p.is_resource = 0
            )";
         
         // Now we add ourself as owner of our own company
         $sql[] = "replace into MyCompanyPermissions (company_id,role) values ('".addslashes($company_id)."','OWNER')";
         
      } else if ( isCompanyRep() ){
         /**
          * Company representatives can see all companies.
          */
          $company_id = getCompanyID();
          
          // Let's add all companies
          $sql[] = "replace into MyCompanyPermissions (company_id,role)
             select company_id, 'SUBSCRIBER' as role from companies";
             
          // Remove all companies that are not resources.
         $sql[] = "delete from MyCompanyPermissions where company_id in (
            select c.company_id from companies c inner join packages p on c.package_id=p.package_id where p.is_resource = 0
            )";
          
          // Now we add our self as REP of our own company
          $sql[] = "replace into MyCompanyPermissions (company_id,role) values ('".addslashes($company_id)."','REP')";
          
         
      } else if ( isAdmin() ){
         /**
          * Admins can, of course, see everything
          */
         
         // Add all companies and make ourself admin.
         $sql[] = "replace into MyCompanyPermissions (company_id,role)
            select company_id, 'ADMIN' as role from companies";
         
         
      
      } else {
      
         /**
          * The public can see all companies
          */
         $sql[] = "replace into MyCompanyPermissions (company_id,role)
            select company_id, 'PUBLIC' as role from companies";
            
         // Remove all companies that are not resources.
         $sql[] = "delete from MyCompanyPermissions where company_id in (
            select c.company_id from companies c inner join packages p on c.package_id=p.package_id where p.is_resource = 0
            )";
      
      }
      
      
      foreach ($sql as $query){
         $res = mysql_query($query, df_db());
         if ( !$res ){
            trigger_error(mysql_error(df_db()).' while executing SQL query "'.$query.'"', E_USER_ERROR);
         }
      }
      
      /**
       * Now we set the security filter so that we only show companies
       * for which we have an assiged role.
       * @see __sql__() to see how the MyRole column is calculated.
       */
       
      $table->setSecurityFilter(array('MyRole'=>'>'));
      
   }
   
   function __sql__(){
      return "select c.*, mcp.role as MyRole, mcp.approval_level from companies c left join MyCompanyPermissions mcp on c.company_id=mcp.company_id";
   }
   
   function getPermissions(&$record){
      
      if ( !$record ){
         // No record is set
         if ( isAdmin() ) return Dataface_PermissionsTool::getRolePermissions('ADMIN');
         if ( isCompanyOwner() or isBrokerDealer() ){
            return Dataface_PermissionsTool::getRolePermissions('OWNER');
         } else if ( isCompanyRep() ){
            return Dataface_PermissionsTool::getRolePermissions('POSTER');
         } else {
            return Dataface_PermissionsTool::getRolePermissions('PUBLIC');
         }
         
      } else {
         // The record is set so we use the role as specified in the record.
         $role = $record->val('MyRole');
         if ( $role ){
            return Dataface_PermissionsTool::getRolePermissions($role);
         } else if ( isAdmin() ){
            return Dataface_PermissionsTool::getRolePermissions('ADMIN');
         } else {
            return Dataface_PermissionsTool::NO_ACCESS();
         }
      
      }
   }
   
   function rel_users__permissions(&$record){
      if ( isAdmin() ) return Dataface_PermissionsTool::ALL();
      return Dataface_PermissionsTool::READ_ONLY();
   }
   
   function views__permissions(&$record){
      return Dataface_PermissionsTool::READ_ONLY();
   }
   
   function package_id__permissions(&$record){
      if ( isAdmin() ){
         return Dataface_PermissionsTool::ALL();
      } else {
         return Dataface_PermissionsTool::NO_ACCESS();
      }
   }
   
   function approval_level__renderCell(&$record){
      if ( !isBrokerDealer() ) return 'N/A';
      switch ($record->val('approval_level') ){
         case 0: return '<img src="images/delete_icon.gif" alt="Not approved"/> <em>(Not Approved)</em>';
         case 1: return '<img src="images/workflow.gif" alt="Conditionally Approved"/> <em>(Conditionally Approved)</em>';
         case 2: return '<img src="images/confirm_icon.gif" alt="Approved"/> <em>(Approved)</em>';
      }
      return '';
   }
   
   function company_name__htmlValue(&$record){
      return '<a href="'.$record->getURL('-action=view').'" title="View company profile">'.$record->display('company_name').'</a>';
   }
   
   function resource_type__htmlValue(&$record){
      if ( $record->val('resource_type') ){
         return '<a href="'.DATAFACE_SITE_HREF.'?-action=list&-table=companies&resource_type='.$record->val('resource_type').'" title="Browse listings of other '.htmlspecialchars($record->display('resource_type')).' companies">'.htmlspecialchars($record->display('resource_type')).'</a> - ( '.$record->htmlValue('subcategories').' )';
      } else {
         return '';
      }
   }
   
   function subcategories__htmlValue(&$record){
      $subcats = $record->val('subcategories');
      $vocab =& $record->_table->getValuelist($record->_table->_fields['subcategories']['vocabulary']);
      $out = array();
      foreach ($subcats as $cat){
         $out[] = '<a href="'.DATAFACE_SITE_HREF.'?-table=companies&subcategories='.$cat.'">'.$vocab[$cat].'</a>';
      }   
      
      if ( count($out) == 0 ) return null;
      else return implode(', ',$out);
   }
   
   function showSummary(&$record){
      $fields = array('company_name','resource_type','mailing_address','main_phone','main_website');
      if ( $record->val('company_logo') ){
         $logourl = $record->display('company_logo');
      } else {
         $logourl = DATAFACE_URL.'/images/missing_logo.gif';
      }
      
      $out = '<a title="View company profile" href="'.$record->getURL('-action=view').'"><img src="'.$logourl.'" style="float: left; padding: 1em" width="100"/></a>';
      
      $out .= '<table class="record-view-table"><tbody>';
      foreach ($fields as $fieldname){
         $field =& $record->_table->getField($fieldname);
         if ( $record->val($fieldname) ){
            $out .= '<tr><th class="record-view-label summary-view-label">'.htmlspecialchars($field['widget']['label']).'</th><td class="record-view-value summary-view-value record-view-value-'.$fieldname.' summary-view-value-'.$fieldname.'">'.$record->htmlValue($fieldname).'</td></tr>';
         }
         unset($field);
      }
      $out .= '</tbody></table>';
      $recent_news = $record->val('recent_news_3');
      if ( count($recent_news) > 0 ){
         $out .= '<dl class="summary-news-list"><dt>Recent News</dt>';
         foreach ($recent_news as $newsitem){
            $out .= '<dd><a href="'.$newsitem->getURL('-action=view').'">'.$newsitem->getTitle().'</a> <span class="published_date"> - '.df_offset(date('Y-m-d H:i:s',$newsitem->getLastModified())).'</span></dd>';
            
         }
         $out .= '</dl>';
      }
      
      return $out;
      
   }
   
   function block__after_summary_actions($params=array()){
      if ( isBrokerDealer() ){
         echo '<div style="width: 150px">'.$this->printApprovalLevel($params['record']).'</div>';
      }
   }
   
   function block__before_result_list(){
      
      
      if ( isBrokerDealer() ){
         $at =& Dataface_ActionTool::getInstance();
         $actions = $at->getActions(array('category'=>'bd_company_filters'));
         echo '<h2>Resources</h2><select onchange="window.location=this.options[this.selectedIndex].value" class="company_filters">';
         echo '<option value="#">Filter Resources</option>';
         foreach ($actions as $action){
            echo '<option value="'.$action['url'].'">'.$action['label'].'</option>';
         }
         echo '</select>';
      } else {
         echo '<h2>Resources</h2>';
      }
      $this->print_subcategories();
   
   }
   
   function block__after_result_list(){
      $this->print_subcategories();
   }
   
   function block__after_edit_record_form(){
      echo '<script language="javascript"><!--
      WLS_resourceTypeChanged(document.getElementById(\'resource_type\'));
      //--></script>';
   }
   
   function printApprovalLevel(&$record){
      switch ($record->val('approval_level') ){
      
         case 0: return "<a title=\"  This means that your agents will not see this company or any of its updates.\">This company is currently not approved.</a>";
         case 1: return "<a title=\"  This means that your agents can see this company, but they will not see its news unless it is explicitly approved by you.\">This company is currently conditionally approved by you.</a>";
         case 2: return "<a title=\"  This means that your agents will be able to see this company and all of its updates, unless you explicitly unapprove them\">This company is currently approved.</a>";
         
      }
   }
   
   
   
   function block__view_tab_content2(){
      $app =& Dataface_Application::getInstance();
      $rec =& $app->getRecord();
      df_display(array('company'=>&$rec), 'company_view.html');
      
   }
   
   function block__before_view_tab_content(){
      
      $app =& Dataface_Application::getInstance();
      $rec =& $app->getRecord();
      
   
      $res = mysql_query("update companies set `views`=`views`+1 where company_id='".addslashes($rec->val('company_id'))."' limit 1", df_db());
      
   }
   
   function field__modified_date_relative(&$record){
      return off($record->strval('modified_date'));
      
   }
   
   
   function field__recent_news(&$record){
      return df_get_records_array('news', array('company_id'=>$record->val('company_id'), 'owner_type'=>'COMPANY','-sort'=>'published_date desc','-limit'=>10));
   }
   
   function field__recent_news_3(&$record){
      return df_get_records_array('news', array('company_id'=>$record->val('company_id'), 'owner_type'=>'COMPANY','-sort'=>'published_date desc','-limit'=>3));
   }
   
   function field__mailing_address(&$record){
      $out = array();
      extract($record->vals());
      if ( $mailing_address_1 ) $out[] = $mailing_address_1;
      if ( $mailing_address_2 ) $out[] = $mailing_address_2;
      if ( $mailing_city ) $out[] = $mailing_city.', '.$mailing_state;
      if ( $mailing_country ) $out[] = $mailing_country;
      if ( $mailing_postal ) $out[] = $mailing_postal;
      return implode("\n", $out);
   }
   
   function field__package(&$record){
      $pid = $record->val('package_id');
      if ( $pid ){
         $package =& df_get_record('packages', array('package_id'=>$pid));
         
      } else {
         $package = null;
      }
      return $package;
   }
   
   function field__max_members(&$record){
      $package =& $record->val('package');
      if ( $package ){
         return $package->val('num_users');
      } else {
         return 5;
      }
   }
   
   function field__max_admins(&$record){
      $package =& $record->val('package');
      if ( $package ) return $package->val('num_admins');
      else return 1;
   }
   
   function field__num_existing_users(&$record){
      $company =& $record;
      $sql = "select count(*) as num, cu.role from users u inner join company_users cu on cu.user_id=u.user_id where cu.company_id='".addslashes($company->val('company_id'))."' group by cu.role";
      $res = mysql_query($sql, df_db());
            
      $roles = array();
      while ($row = mysql_fetch_assoc($res) ) $roles[$row['role']] = $row['num'];

            
            
      if ( !isset($roles['ADMIN']) ) $roles['ADMIN'] = 0;
      if ( !isset($roles['MEMBER']) ) $roles['MEMBER'] = 0;
      return $roles;
   }
   
   function field__num_admins(&$record){
      $roles = $record->val('num_existing_users');
      return $roles['ADMIN'];
   }
   
   function field__num_members(&$record){
      $roles = $record->val('num_existing_users');
      return $roles['MEMBER'];
   }
   
   function section__recent_news(&$record){
      
      return array(
         'class'=>'left',
         'records'=>$record->val('recent_news'),
         'label'=>'Recent Updates',
         'url'=>DATAFACE_SITE_HREF.'?-action=list&-table=news&company_id='.$record->val('company_id').'&owner_type=COMPANY'
      );
   }
   
   
   function section__profile_page_1(&$record){
      $query = array('company_id'=>$record->val('company_id'),'owner_type'=>'COMPANY', 'active'=>1,'-limit'=>1,'-skip'=>0);
      $pages = df_get_records_array('profile_pages',$query);
      //echo "Num Profile Pages: ".count($pages);
      //$page = df_get_record('profile_pages', $query);
      if ( count($pages) > 0 ){
         $content =  $pages[0]->val('approved_content');
         if ( !$content ) return null;
         return array(
         'class'=>'main',
         'content'=> $content,
         'label'=> $pages[0]->strval('title'),
         'edit_url'=>( $pages[0]->checkPermission('edit') ? $pages[0]->getURL('-action=edit') : null)
         );
      } else {
         return null;
      }
      
   }
   
   
   function section__profile_page_2(&$record){
      $query = array('company_id'=>$record->val('company_id'),'owner_type'=>'COMPANY', 'active'=>1,'-limit'=>1,'-skip'=>1);
      $pages = df_get_records_array('profile_pages',$query);
      //echo "Num Profile Pages: ".count($pages);
      //$page = df_get_record('profile_pages', $query);
      if ( count($pages) > 0 ){
         $content =  $pages[0]->val('approved_content');
         if ( !$content ) return null;
         return array(
         'class'=>'main',
         'content'=> $content,
         'label'=> $pages[0]->strval('title'),
         'edit_url'=>( $pages[0]->checkPermission('edit') ? $pages[0]->getURL('-action=edit') : null)
         );
      } else {
         return null;
      }
      
   }
   
   function section__stats(&$record){
      if ( $record->checkPermission('view_stats') ){
         $sql = "select news_id, headline, views from news where company_id='".addslashes($record->val('company_id'))."' and owner_type='COMPANY'";
         $res = mysql_query($sql, df_db());
         $stats = array();
         while ( $row = mysql_fetch_assoc($res) ) $stats[] = $row;
         @mysql_free_result($res);
         
         ob_start();
         echo '<table class="record-view-table"><thead><tr><th>News</th><th>View Count</th></tr></thead><tbody>';
         echo '<tr><th class="record-view-label">Company Profile</th><td>'.$record->val('views').'</td></tr>';
         foreach ($stats as $stat){
            echo '<tr><th class="record-view-label"><a href="'.DATAFACE_SITE_HREF.'?-action=view&-table=news&news_id='.$stat['news_id'].'">'.htmlspecialchars($stat['headline']).'</a></th><td class="record-view-value">'.$stat['views'].'</a></td></tr>';
         }
         echo '</tbody></table>';
         
         $content = ob_get_contents();
         ob_end_clean();
         return array(
            'class'=>'left',
            'content'=>$content,
            'label'=>'Statistics'
            );
      }
      return null;
   }
   
   function beforeAddRelatedRecord(&$record){
      switch ($record->_relationshipName){
         case 'users':
            $company =& $record->getParent();
            $max_members = $company->val('max_members');
            $max_admins = $company->val('max_admins');
            
            $sql = "select count(*) as num, cu.role from users u inner join company_users cu on cu.user_id=u.user_id where cu.company_id='".addslashes($company->val('company_id'))."' group by cu.role";
            $res = mysql_query($sql, df_db());
            
            $roles = array();
            while ($row = mysql_fetch_assoc($res) ) $roles[$row['role']] = $row['num'];

            
            
            if ( !isset($roles['ADMIN']) ) $roles['ADMIN'] = 0;
            if ( !isset($roles['MEMBER']) ) $roles['MEMBER'] = 0;
            
            $vals = $record->getAbsoluteValues();
            $thisrole = $vals['company_users.Role'];
            //print_r($vals);exit;
            //echo $thisrole;exit;
            if ( $thisrole == 'ADMIN' and $max_admins <= $roles['ADMIN'] ){
               return PEAR::raiseError("This company can have a maximum of $max_admins administrator users, and it already has $roles[ADMIN] .", DATAFACE_E_NOTICE);
               
            } else if ($thisrole == 'MEMBER' and $max_members <= $roles['MEMBER'] ){
               return PEAR::raiseError("This company can have a maximum of $max_members member users and it already has $roles[MEMBER] .", DATAFACE_E_NOTICE);
            }
         break;
            
            
      
      }
   }
   
   
   
   
   
   
   function block__before_related_users_records_list(){
      $app =& Dataface_Application::getInstance();
      $record =& $app->getRecord();
      if ( $record ){
         echo '<p>Your company has '.($record->val('max_members') - $record->val('num_members')).' available member accounts.</p>';
      }
   }
   
   function print_subcategories(){
      static $count = 0;
      static $html = '';
      if ( $count === 0 ){
         $count = 1;
         
         $app =& Dataface_Application::getInstance();
         $query = $app->getQuery();
         unset($query['subcategories']);
         $companies_qb =& new Dataface_QueryBuilder($query['-table']);
         
         $sql = "select ct.subtype_name, ct.subtype_id, count(*) as num ".$companies_qb->_from()." inner join company_subtypes ct on (companies.subcategories rlike CONCAT('[[:<:]]',ct.subtype_id,'[[:>:]]')) ".$companies_qb->_secure($companies_qb->_where($query))." group by ct.subtype_name, ct.subtype_id";
         $res = mysql_query($sql, df_db());
         if ( !$res ) trigger_error(mysql_error(df_db()), E_USER_ERROR);
         $cats = array();
         while ($row = mysql_fetch_assoc($res) ) $cats[] = $row;
         
         $html .= '<div style="clear:both"><b>Filter by type:</b><ul class="subcategories-menu">';
         foreach ( $cats as $cat ){
            $query['subcategories'] = $cat['subtype_id'];
            $html .= '<li><a href="'.$app->url($query).'">'.$cat['subtype_name'].' ('.$cat['num'].')</a></li>';
         }
         $html .= '</ul></div>';
      }
      echo $html;
      
   }
   
   function __import__excel_spreadsheet(&$data, $defaults=array()){
   
      import('tables/companies/importer.php');
      $importer =& new tables_companies_importer();
      $importer->import($data, $defaults);
      //echo "Imported ".count($importer->records)." records.";exit;
      return $importer->records;
   }
   
   
   
   
   
   
}
?>