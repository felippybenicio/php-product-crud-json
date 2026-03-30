<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>cadastro e listagem de produto</title>
    <link rel="stylesheet" href="estilos/cadastrado.css">
</head>
<body>
    <?php 
        $data = date("d/m/Y");
        $hora = date("H:i:s");
        $produto = $_GET["nomeProduto"] ;
        $descrição = $_GET["descrição"] ?? '';
        $valor = number_format($_GET['valor'], 2, ",", ".");

        $disponibilidade = $_GET["disponibilidade"];
        $nao = false;
        $sim = false;

        if (isset($disponibilidade)) {
            if ($disponibilidade === 'Sim') {
                $sim = true;
            } elseif ($disponibilidade === 'Não') {
                $nao = true;
            }
        }

        
       
        // Caminho do arquivo JSON
        $caminhoArquivo = './dados.json';


        // Lê o conteúdo atual do arquivo JSON
        $conteudoJson = file_get_contents($caminhoArquivo);
        $dados = json_decode($conteudoJson, true);

        // Garante que o conteúdo é um array
        if (!is_array($dados)) {
            $dados = [];
        }

        // Dados recebidos do formulário
        $produto = isset($_GET['nomeProduto']) ? trim($_GET['nomeProduto']) : null;
        $descricao = isset($_GET['descrição']) ? trim($_GET['descrição']) : null;
        $valor = isset($_GET['valor']) ? trim($_GET['valor']) : null;
        $disponibilidade = isset($_GET['disponibilidade']) ? trim($_GET['disponibilidade']) : null;

        // Verifica se os dados do formulário estão preenchidos
        if ($produto && $descricao && $valor && $disponibilidade) {
            // Verifica se o produto já existe no JSON
            $produtoJaExiste = false;
            foreach ($dados as $item) {
                if (strtolower($item['produto']) === strtolower($produto)) { // Verifica pelo nome do produto
                    $produtoJaExiste = true;
                    break;
                }
            }

            // Adiciona o novo produto se ele não existir
            if (!$produtoJaExiste) {
                $novoProduto = [
                    "produto" => $produto,
                    "descricao" => $descricao,
                    "valor" => number_format($valor, 2, ",", "."),
                    "disponibilidade" => $disponibilidade,
                    "data" => date("d/m/Y"),
                    "hora" => date("H:i:s"),
                    
                ];
                array_unshift($dados, $novoProduto); // Adiciona o produto no início do array

                // Atualiza o arquivo JSON
                file_put_contents($caminhoArquivo, json_encode($dados, JSON_PRETTY_PRINT));
            }
        }

        // Gera a string formatada para exibição dos produtos
        echo "<section>";
            $listagemProdutos = '';
            if (is_array($dados) && count($dados) > 0) {
                foreach ($dados as $itens) {
                    $listagemProdutos .= "
                        <main class='jaCadastrados'>
                            <table>
                                <tr>
                                    <td><h2>{$itens['produto']}</h2></td>
                                </tr>
                                <tr>
                                    <td><p><strong>Descrição do Produto:</strong></p> {$itens['descricao']}</td>
                                </tr>
                                <tr class='valores'>
                                    <td><p class='valor'><strong>Valor:</strong></p> R\${$itens['valor']}</td>
                                </tr>
                                <tr>
                                    <td><p>Disponível:</p> {$itens['disponibilidade']}</td>
                                </tr>
                                <abbr title='Excluir Produto'><img src='imagens/lixo.png' alt='lixeira' id='lixeira' onclick='deletar()'></abbr>
                                <tr>
                                <tr>
                                    <td><p class='dataHora'><strong>Cadastrado em:</strong>{$itens['data']}
                                    às {$itens['hora']}</p></td>
                                </tr>
                            </table>
                        </main>";
                }
            }
        echo "</section>";
        
        if($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_index'])){

            $conteudoJson = file_get_contents($caminhoArquivo);
            $dados = json_decode($conteudoJson, true);


            $deleteIndex = (int)
            $_GET['delete_index'];
            if (isset($dados[$deleteIndex])){
                unset($dados[$deleteIndex]);
                $dados = array_values($dados);
                file_put_contents($caminhoArquivo, json_encode($dados, JSON_PRETTY_PRINT));
            }
        }

        foreach($dados as $indice => $itens) {
           
            echo "  <input type='hidden' name='delete_index' value='$indice'>
            
            <button type='submit'>
                <abbr title='Excluir Produto'>
                    <img src='imagens/lixo.png' alt='lixeira' id='lixo' onclick='deletar()'>
                </abbr>
            </button>";

        }


         

        echo "$listagemProdutos";

        echo"
            <main>
                <table>
                    <tr>
                        <td><h2>$produto</h2></td>
                    </tr>
                    <tr>
                        <td><p><strong>descrição do Produto:</strong></p>
                        $descrição</td>
                    </tr>
                    <tr  id ='valor'>
                        <td><p class='valor'><strong>valor:</strong></p>R\$$valor</td>
                    </tr>";
                    if ($nao) {
                        echo "<tr>
                            <td><p><strong>Disponível:</strong></p> $disponibilidade\u{274C} </td>
                        </tr>";
                    } elseif ($sim) {
                        echo "<tr>
                            <td><p><strong>Disponível:</strong></p> $disponibilidade\u{2705}</td>
                        </tr>";
                    }; 
                    echo 
                    "<tr>
                        <td><button onclick='cadastrar()' id='paraCadastrar'>Cadastrar novo produto</button></td>
                    </tr>;
                    <tr class='dataHora'>
                        <td ><strong><p class='dataHora'>cadastrado: $data às $hora</strong> </p></td>
                    </tr>
                </table>
            </main>";
     ?>        
    <script>
        function cadastrar() {
            const paginaParaCadastrar = document.getElementById('paraCadastrar')

            paginaParaCadastrar.addEventListener('click', cadastrar)

            window.location.href = 'index.html'
        }

        

        function deletar() {
                    function deletar(index) {
    // Exibe a confirmação antes de realizar a exclusão
    const confirmacao = confirm('Tem certeza que deseja excluir?');
    
    if (!confirmacao) {
        return; // Sai da função se o usuário clicar em "Cancelar"
    }

    // Faz a requisição para excluir o produto
    fetch(`?delete_index=${index}`)
        .then(response => {
            if (response.ok) {
                // Remove o elemento correspondente da interface (DOM)
                const produtoElement = document.querySelector(`#produto-${index}`);
                if (produtoElement) {
                    produtoElement.remove();
                } else {
                    alert("Erro: Produto não encontrado na interface.");
                }
            } else {
                alert("Erro ao excluir o produto no servidor!");
            }
        })
        .catch(error => {
            console.error("Erro na requisição:", error);
            alert("Erro ao processar a exclusão.");
        });
}

            
            
        }
    
    </script>
    
</body>
</html>
