<?php
// defined('BASEPATH') OR exit('No direct script access allowed');

class Assinatura extends CI_Controller
{
    public function upload_signature()
    {
        // Carrega a Model de OS
        $this->load->model('os_model');
        // Carrega a biblioteca de validação
        $this->load->library('form_validation');
        // Define as validações
        $this->form_validation->set_rules('idOs', 'idOs', 'required');
        $this->form_validation->set_rules('assClienteImg', 'valid_base64');
        $this->form_validation->set_rules('assTecnicoImg', 'valid_base64');

        // Define o padrão da resposta
        $response = [
            'code'    => '400',
            'success' => false
        ];

        // Executa a validação dos dados recebidos. Se a validação falhar (false) retorna erro 400
        if ($this->form_validation->run() == false) {
            $response['message'] = 'Erro: Corrija os dados das assinaturas e tente novamente.';
            $this->response_signature($response);
        }

        // Seta as variáveis que precisaremos
        $idOs          = $this->input->post('idOs'); //ID da OS
        $signaturesDir = realpath(APPPATH . '../') . '/assets/assinaturas/'; // Caminho onde as assinaturas serão salvas
        
        // Cria o diretório caso não exista
        if (!is_dir($signaturesDir)) {
            mkdir($signaturesDir, 0777, true);
        }

        if(!empty($this->input->post('assClienteImg'))){
            $assClienteImg = preg_replace('#^data:image/[^;]+;base64,#', '', $this->input->post('assClienteImg')); // Imagem da assinatura do cliente
            $data['assClienteIp'] = $this->input->ip_address();
            $data['assClienteData'] = date('Y-m-d H:i:s');

            // Define o nome da imagem de assinatura do cliente "IDdaOS_DataNoFormatoAnomêsdiahoraminutosegundo_NumeroAleatorioDe4Digitos.png"
            $assClienteImgName = $idOs . '_' . date('YmdHis') . '_' . rand(1000,9999) . '.png';
            $assClientePatch = $signaturesDir . $assClienteImgName;

            // Se conseguir salvar a assinatura do cliente na pasta, coloca o nome final do arquivo na variável
            if(file_put_contents($assClientePatch, base64_decode($assClienteImg))) {
                $data['assClienteImg'] = $assClienteImgName;
            } else {
                $response['message'] = 'Erro: Falha ao salvar assinatura do cliente.';
                $this->response_signature($response);
            }
        }

        if(!empty($this->input->post('assTecnicoImg'))){
            $assTecnicoImg = preg_replace('#^data:image/[^;]+;base64,#', '', $this->input->post('assTecnicoImg')); // Imagem da assinatura do técnico
            $data['assTecnicoIp'] = $this->input->ip_address();
            $data['assTecnicoData'] = date('Y-m-d H:i:s');

            // Define o nome da imagem de assinatura do técnico "IDdaOS_DataNoFormatoAnomêsdiahoraminutosegundo_NumeroAleatorioDe4Digitos.png"
            $assTecnicoImgName = $idOs . '_' . date('YmdHis') . '_' . rand(1000,9999) . '.png';
            $assTecnicoPatch = $signaturesDir . $assTecnicoImgName;
        
            // Se conseguir salvar a assinatura do técnico na pasta, coloca o nome final do arquivo na variável
            if(file_put_contents($assTecnicoPatch, base64_decode($assTecnicoImg))) {
                $data['assTecnicoImg'] = $assTecnicoImgName;
            } else {
                $response['message'] = 'Erro: Falha ao salvar assinatura do técnico.';
                $this->response_signature($response);
            }
        }

        // Se conseguir persistir os dados no banco, responde com sucesso
        if ($this->os_model->edit('os', $data, 'idOs', $this->input->post('idOs')) == true) {
            // Se chegou até aqui, a assinatura foi salva com sucesso
            $response = [
                'code'    => '200',
                'success' => true,
                'message' => 'Assinaturas salvas com sucesso.'
            ];
        }else{
            $response['message'] = 'Erro: Falha ao salvar os dados da assinatura.';
        }
        
        $this->response_signature($response);
    }

    protected function response_signature($response)
    {
        return $this->output
        ->set_status_header($response['code'])
        ->set_content_type('application/json', 'utf-8')
        ->set_output(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}