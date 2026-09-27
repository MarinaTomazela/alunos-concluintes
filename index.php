<?php

require_once 'conexao.php';

/*
 * Recebe o conteúdo do campo de busca.
 * Caso nada tenha sido informado, o valor será vazio.
 */
$busca = isset($_GET['busca'])
    ? trim($_GET['busca'])
    : '';

/*
 * Recebe a opção de ordenação.
 * O padrão é ordenar os alunos pelo código.
 */
$ordem = $_GET['ordem'] ?? 'codigo';

/*
 * A média é calculada somando as quatro notas
 * e dividindo o resultado por quatro.
 */
$calculoMedia = "
    (nota1 + nota2 + nota3 + nota4) / 4
";

/*
 * Define a ordenação da consulta.
 *
 * A opção "media" gera o ranking da maior média
 * para a menor média.
 *
 * As opções não são colocadas diretamente pelo usuário
 * na consulta. Dessa forma, evitamos comandos SQL inválidos.
 */
switch ($ordem) {
    case 'media':
        $ordenacao = 'media DESC, nome ASC';
        break;

    case 'nome':
        $ordenacao = 'nome ASC';
        break;

    default:
        $ordem = 'codigo';
        $ordenacao = 'idalunoconcluinte ASC';
        break;
}

/*
 * Consulta principal.
 *
 * A cláusula WHERE somente será adicionada quando houver
 * algum nome informado no campo de busca.
 */
$sql = "
    SELECT
        idalunoconcluinte,
        nome,
        nota1,
        nota2,
        nota3,
        nota4,
        {$calculoMedia} AS media
    FROM alunoconcluinte
";

if ($busca !== '') {
    $sql .= ' WHERE nome LIKE :busca';
}

$sql .= " ORDER BY {$ordenacao}";

/*
 * Prepara e executa a consulta.
 */
$comando = $conexao->prepare($sql);

if ($busca !== '') {
    $comando->bindValue(
        ':busca',
        '%' . $busca . '%',
        PDO::PARAM_STR
    );
}

$comando->execute();

/*
 * fetchAll retorna todos os alunos encontrados
 * em forma de array.
 */
$alunos = $comando->fetchAll();

/*
 * Informações usadas nos cartões de resumo.
 */
$totalAlunos = count($alunos);
$somaMedias = 0;

foreach ($alunos as $aluno) {
    $somaMedias += (float) $aluno['media'];
}

$mediaTurma = $totalAlunos > 0
    ? $somaMedias / $totalAlunos
    : 0;

/*
 * Função para proteger os textos exibidos na página.
 */
function escapar(string $texto): string
{
    return htmlspecialchars(
        $texto,
        ENT_QUOTES,
        'UTF-8'
    );
}

/*
 * Define uma classe visual de acordo com a média.
 */
function classeMedia(float $media): string
{
    if ($media >= 7) {
        return 'media-alta';
    }

    if ($media >= 5) {
        return 'media-regular';
    }

    return 'media-baixa';
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="description"
        content="Consulta das notas dos alunos concluintes."
    >

    <title>Alunos Concluintes</title>

    >
</head>

<body>
    <header class="cabecalho">
        <div class="container">
            <div class="marca">
                <span class="marca-simbolo" aria-hidden="true">
                    AC
                </span>

                <div>
                    <strong>Alunos Concluintes</strong>
                    <span>Sistema de consulta de notas</span>
                </div>
            </div>
        </div>
    </header>

    <main class="container conteudo">
        <section class="introducao">
            <div>
                <span class="etiqueta">Programação Web II</span>

                <h1>Notas dos alunos concluintes</h1>

                <p>
                    Consulte as notas dos quatro módulos e a
                    média final de cada aluno.
                </p>
            </div>
        </section>

        <section
            class="resumo"
            aria-label="Resumo dos resultados"
        >
            <article class="resumo-item">
                <span>Alunos encontrados</span>

                <strong>
                    <?= $totalAlunos ?>
                </strong>
            </article>

            <article class="resumo-item">
                <span>Média da turma</span>

                <strong>
                    <?= number_format(
                        $mediaTurma,
                        1,
                        ',',
                        '.'
                    ) ?>
                </strong>
            </article>

            <article class="resumo-item">
                <span>Ordenação atual</span>

                <strong class="texto-resumo">
                    <?php if ($ordem === 'media'): ?>
                        Maior média
                    <?php elseif ($ordem === 'nome'): ?>
                        Nome
                    <?php else: ?>
                        Código
                    <?php endif; ?>
                </strong>
            </article>
        </section>

        <section class="painel">
            <div class="painel-cabecalho">
                <div>
                    <h2>Consulta de alunos</h2>

                    <p>
                        Digite um nome ou altere a ordenação
                        da tabela.
                    </p>
                </div>
            </div>

            <form
                class="formulario-busca"
                action="index.php"
                method="get"
            >
                <div class="grupo-campo campo-busca">
                    abel for="busca">
                        Nome do aluno
                    </label>

                    <input
                        type="search"
                        id="busca"
                        name="busca"
                        value="<?= escapar($busca) ?>"
                        placeholder="Exemplo: Eduarda"
                    >
                </div>

                <div class="grupo-campo">
                    abel for="ordem">
                        Ordenar por
                    </label>

                    <select id="ordem" name="ordem">
                        <option
                            value="codigo"
                            <?= $ordem === 'codigo'
                                ? 'selected'
                                : '' ?>
                        >
                            Código
                        </option>

                        <option
                            value="nome"
                            <?= $ordem === 'nome'
                                ? 'selected'
                                : '' ?>
                        >
                            Nome
                        </option>

                        <option
                            value="media"
                            <?= $ordem === 'media'
                                ? 'selected'
                                : '' ?>
                        >
                            Maior média
                        </option>
                    </select>
                </div>

                <button
                    class="botao botao-pesquisar"
                    type="submit"
                >
                    Pesquisar
                </button>

                <?php if ($busca !== '' || $ordem !== 'codigo'): ?>
                    <a
                        class="botao botao-limpar"
                        href="index.php"
                    >
                        Limpar
                    </a>
                <?php endif; ?>
            </form>

            <?php if ($busca !== ''): ?>
                <p class="resultado-busca">
                    Resultado da pesquisa por:
                    <strong><?= escapar($busca) ?></strong>
                </p>
            <?php endif; ?>

            <?php if (count($alunos) > 0): ?>
                <div class="tabela-container">
                    <table>
                        aption class="somente-leitor">
                            Notas e médias dos alunos concluintes
                        </caption>

                        <thead>
                            <tr>
                                <?php if ($ordem === 'media'): ?>
                                    <th scope="col">Posição</th>
                                <?php endif; ?>

                                <th scope="col">Código</th>
                                <th scope="col">Nome</th>
                                <th scope="col">Nota 1</th>
                                <th scope="col">Nota 2</th>
                                <th scope="col">Nota 3</th>
                                <th scope="col">Nota 4</th>
                                <th scope="col">Média</th>
                                <th scope="col">Situação</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            $posicao = 1;

                            foreach ($alunos as $aluno):
                                $media = (float) $aluno['media'];
                                $classe = classeMedia($media);
                            ?>
                                <tr>
                                    <?php if ($ordem === 'media'): ?>
                                        <td
                                            data-label="Posição"
                                            class="posicao"
                                        >
                                            <?= $posicao ?>º
                                        </td>
                                    <?php endif; ?>

                                    <td data-label="Código">
                                        <?= (int)
                                            $aluno[
                                                'idalunoconcluinte'
                                            ] ?>
                                    </td>

                                    <td
                                        data-label="Nome"
                                        class="nome-aluno"
                                    >
                                        <?= escapar(
                                            $aluno['nome']
                                        ) ?>
                                    </td>

                                    <td data-label="Nota 1">
                                        <?= number_format(
                                            (float) $aluno['nota1'],
                                            1,
                                            ',',
                                            '.'
                                        ) ?>
                                    </td>

                                    <td data-label="Nota 2">
                                        <?= number_format(
                                            (float) $aluno['nota2'],
                                            1,
                                            ',',
                                            '.'
                                        ) ?>
                                    </td>

                                    <td data-label="Nota 3">
                                        <?= number_format(
                                            (float) $aluno['nota3'],
                                            1,
                                            ',',
                                            '.'
                                        ) ?>
                                    </td>

                                    <td data-label="Nota 4">
                                        <?= number_format(
                                            (float) $aluno['nota4'],
                                            1,
                                            ',',
                                            '.'
                                        ) ?>
                                    </td>

                                    <td data-label="Média">
                                        <span
                                            class="media <?= $classe ?>"
                                        >
                                            <?= number_format(
                                                $media,
                                                1,
                                                ',',
                                                '.'
                                            ) ?>
                                        </span>
                                    </td>

                                    <td data-label="Situação">
                                        <?php if ($media >= 5): ?>
                                            <span
                                                class="situacao aprovado"
                                            >
                                                Aprovado
                                            </span>
                                        <?php else: ?>
                                            <span
                                                class="situacao reprovado"
                                            >
                                                Reprovado
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>

                                <?php $posicao++; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="estado-vazio">
                    <h3>Nenhum aluno encontrado</h3>

                    <p>
                        Verifique o nome digitado e faça uma
                        nova pesquisa.
                    </p>

                    <a class="botao botao-pesquisar" href="index.php">
                        Mostrar todos
                    </a>
                </div>
            <?php endif; ?>
        </section>

        <section class="legenda">
            <h2>Legenda das médias</h2>

            <ul>
                >
                    <span class="indicador indicador-verde"></span>
                    Média igual ou maior que 7
                </li>

                >
                    <span class="indicador indicador-amarelo"></span>
                    Média entre 5 e 6,9
                </li>

                >
                    <span class="indicador indicador-vermelho"></span>
                    Média menor que 5
                </li>
            </ul>
        </section>
    </main>

    <footer class="rodape">
        <p>
            Atividade de Programação Web II —
            Alunos Concluintes
        </p>
    </footer>
</body>
</html>