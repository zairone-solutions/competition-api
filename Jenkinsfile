pipeline {
    agent any
    stages {
        stage('Verify tooling') {
            steps {
                sh '''
                    docker info
                    docker version
                    docker compose version
                '''
            }
        }
        stage('Verify SSH connection to server') {
            steps {
                sshagent(credentials: ['aws-ec2']) {
                    sh '''
                        ssh -o StrictHostKeyChecking=no ec2-user@54.172.176.249 whoami
                    '''
                }
            }
        }
        stage('Clear all running docker containers') {
            steps {
                script {
                    try {
                        sh 'docker rm -f $(docker ps -a -q)'
                    } catch (Exception e) {
                        echo 'No running container to clear up...'
                    }
                }
            }
        }
        stage('Populate .env file') {
            steps {
                dir('/var/lib/jenkins/workspace/envs/uniquo-test') {
                    fileOperations([fileCopyOperation(excludes: '', flattenFiles: true, includes: '.env', targetLocation: "${WORKSPACE}")])
                }
            }
        }
        stage('Start Docker') {
            steps {
                sh 'make up'
                sh 'docker compose ps'
            }
        }
    }
    post {
        success {
            sh 'cd "/var/lib/jenkins/workspace/UniquoTest"'
            sh 'rm -rf artifact.zip'
            sh 'zip -r artifact.zip . -x "*node_modules**"'

            withCredentials([sshUserPrivateKey(credentialsId: 'aws-ec2', keyFileVariable: 'keyfile')]) {
                sh 'scp -v -o StrictHostKeyChecking=no -i ${keyfile} /var/lib/jenkins/workspace/UniquoTest/artifact.zip ec2-user@54.172.176.249:/home/ec2-user/artifact'

                // sh 'ssh -i ${keyfile} ec2-user@ec2-54-172-176-249.compute-1.amazonaws.com'
                sh 'sudo unzip -o ~/artifact/artifact.zip -d ~/projects/uniquo-test'
                sh 'cd ~/projects/uniquo-test'
                sh 'docker compose down'
                sh 'docker compose up -d --build'

                sh 'sudo chown -R $USER .'
                sh 'docker compose exec uniquo-app composer install'
                sh 'docker compose exec uniquo-app php artisan key:generate'
                sh 'docker compose exec uniquo-app php artisan migrate:refresh --seed'
            }
        }
        always {
            sh 'docker compose down --remove-orphans -v'
            sh 'docker compose ps'
        }
    }
}
