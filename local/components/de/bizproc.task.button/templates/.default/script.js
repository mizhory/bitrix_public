function BPStarter(){
    var self = this;
    self.EntityEditorObj = null;
    
    self.ajaxUrl = "/bitrix/components/bitrix/bizproc.workflow.start/ajax.php"; 
    self.params = [];
    self.params['sessid'] = BX.message('bitrix_sessid');
    self.params['site'] = BX.message('SITE_ID');
    self.params['ajax_action'] = "start_workflow";
    self.params['module_id'] = "crm";
    self.params['entity'] = "CCrmDocumentDeal";
    self.params['document_type'] = "DEAL";      

    self.startWorkflow = function (dealId, templateId){                
        self.params['document_id'] = "DEAL_" + dealId;
        self.params['template_id'] = templateId; 

        BX.ajax({
            method: 'POST',
            dataType: 'json',
            url: self.ajaxUrl,
            data: self.params,
            onsuccess: function(response)
            {
                if(window.UF_BP_TASK_BUTTON_OBJ){
                    window.UF_BP_TASK_BUTTON_OBJ.refreshLayout();             
                }                
                console.log(response);
                /*if (response.success)
                {
                    callback(response.data, response);
                }
                else
                {
                    window.alert(response.errors.join('\n'));
                }*/
            },
        });
    }   
}